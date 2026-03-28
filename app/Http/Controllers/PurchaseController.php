<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchasePaymentRequest;
use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $purchases = Purchase::query()
            ->with(['supplier', 'warehouse'])
            ->withCount('purchaseReturns')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('supplier', function ($s) use ($search) {
                            $s->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('purchase_date', '<=', $dateTo))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('purchases.index', [
            'purchases' => $purchases,
        ]);
    }

    public function create(): View
    {
        return view('purchases.create', [
            'invoiceNo' => Purchase::nextInvoiceNo(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'warehouses' => Warehouse::query()->enabled()->orderBy('name')->get(),
        ]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $data = $this->buildTotalsFromItems($request->validated()['items'], (float) $request->validated()['discount']);
        if ($data['payable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        DB::transaction(function () use ($request, $data) {
            $purchase = Purchase::create([
                'invoice_no' => $request->validated()['invoice_no'],
                'supplier_id' => $request->validated()['supplier_id'],
                'warehouse_id' => $request->validated()['warehouse_id'],
                'purchase_date' => $request->validated()['purchase_date'],
                'note' => $request->validated()['note'] ?? null,
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'],
                'payable_amount' => $data['payable'],
                'paid_amount' => 0,
            ]);

            foreach ($data['lines'] as $line) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $line['product_id'],
                    'product_name' => $line['product_name'],
                    'sku' => $line['sku'],
                    'quantity' => $line['quantity'],
                    'unit_label' => $line['unit_label'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);
            }
        });

        return redirect()->route('purchases.all')->with('success', 'Purchase created successfully.');
    }

    public function edit(Purchase $purchase): View
    {
        $purchase->load(['items.product.unit', 'supplier', 'warehouse']);

        return view('purchases.edit', [
            'purchase' => $purchase,
            'suppliers' => Supplier::orderBy('name')->get(),
            'warehouses' => Warehouse::query()
                ->where(function ($q) use ($purchase) {
                    $q->where('status', true)
                        ->orWhere('id', $purchase->warehouse_id);
                })
                ->orderBy('name')
                ->get(),
            'hasReturns' => $purchase->hasReturns(),
        ]);
    }

    public function update(UpdatePurchaseRequest $request, Purchase $purchase): RedirectResponse
    {
        if ($purchase->hasReturns()) {
            return back()->with('error', 'You cannot edit a purchase after a return has been recorded.');
        }

        $data = $this->buildTotalsFromItems($request->validated()['items'], (float) $request->validated()['discount']);
        if ($data['payable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        if ($data['payable'] < (float) $purchase->paid_amount - 0.0001) {
            return back()->withInput()->withErrors(['discount' => 'Payable cannot be less than amount already paid.']);
        }

        DB::transaction(function () use ($request, $purchase, $data) {
            $purchase->items()->delete();

            $purchase->update([
                'invoice_no' => $request->validated()['invoice_no'],
                'supplier_id' => $request->validated()['supplier_id'],
                'warehouse_id' => $request->validated()['warehouse_id'],
                'purchase_date' => $request->validated()['purchase_date'],
                'note' => $request->validated()['note'] ?? null,
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'],
                'payable_amount' => $data['payable'],
            ]);

            foreach ($data['lines'] as $line) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $line['product_id'],
                    'product_name' => $line['product_name'],
                    'sku' => $line['sku'],
                    'quantity' => $line['quantity'],
                    'unit_label' => $line['unit_label'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);
            }
        });

        return redirect()->route('purchases.all')->with('success', 'Purchase updated successfully.');
    }

    public function storePayment(StorePurchasePaymentRequest $request, Purchase $purchase): RedirectResponse
    {
        $due = $purchase->due_amount;
        $paying = (float) $request->validated()['paying_amount'];
        if ($paying > $due + 0.0001) {
            return back()->withErrors(['paying_amount' => 'Amount cannot exceed the due balance.']);
        }

        DB::transaction(function () use ($purchase, $paying, $request) {
            PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'user_id' => $request->user()?->id,
                'amount' => $paying,
            ]);
            $purchase->increment('paid_amount', $paying);
        });

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function searchProducts(Request $request)
    {
        $q = $request->string('q')->trim()->value();
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $products = Product::query()
            ->with('unit')
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', '%'.$q.'%')
                    ->orWhere('sku', 'like', '%'.$q.'%');
            })
            ->orderBy('name')
            ->limit(25)
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'unit_label' => $p->unit->short_name ?? $p->unit->name,
                'default_purchase_price' => (float) $p->default_purchase_price,
            ]);

        return response()->json($products);
    }

    public function exportPdfAll(Request $request)
    {
        $purchases = $this->filteredPurchasesQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('purchases.pdf.all', [
            'purchases' => $purchases,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('all-purchases-'.time().'.pdf');
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $purchases = $this->filteredPurchasesQuery($request)->get();
        $filename = 'all-purchases-'.time().'.csv';

        return response()->streamDownload(function () use ($purchases) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Invoice No.',
                'Date',
                'Supplier',
                'Mobile',
                'Total Amount',
                'Warehouse',
                'Discount',
                'Payable',
                'Paid',
                'Due',
            ]);
            foreach ($purchases as $p) {
                fputcsv($out, [
                    $p->invoice_no,
                    $p->purchase_date->format('m/d/Y'),
                    $p->supplier->name,
                    $p->supplier->phone ?? '',
                    formatCurrency($p->subtotal),
                    $p->warehouse->name,
                    formatCurrency($p->discount),
                    formatCurrency($p->payable_amount),
                    formatCurrency($p->paid_amount),
                    formatCurrency($p->due_amount),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdfInvoice(Purchase $purchase)
    {
        $purchase->load(['supplier', 'warehouse', 'items.product.unit']);
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('purchases.pdf.invoice', [
            'purchase' => $purchase,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('purchase-'.$purchase->invoice_no.'.pdf');
    }

    /**
     * @param  array<int, array{product_id: int, quantity: float|int|string, unit_price: float|int|string}>  $items
     * @return array{subtotal: float, discount: float, payable: float, lines: list<array<string, mixed>>}
     */
    private function buildTotalsFromItems(array $items, float $discount): array
    {
        $subtotal = 0;
        $lines = [];

        foreach ($items as $row) {
            $product = Product::with('unit')->findOrFail($row['product_id']);
            $qty = (float) $row['quantity'];
            $price = (float) $row['unit_price'];
            $lineTotal = round($qty * $price, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $qty,
                'unit_label' => $product->unit->short_name ?? $product->unit->name,
                'unit_price' => $price,
                'line_total' => $lineTotal,
            ];
        }

        $discount = round($discount, 2);
        $payable = round(max(0, $subtotal - $discount), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'payable' => $payable,
            'lines' => $lines,
        ];
    }

    private function filteredPurchasesQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        return Purchase::query()
            ->with(['supplier', 'warehouse'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('supplier', function ($s) use ($search) {
                            $s->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('purchase_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('purchase_date', '<=', $dateTo))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id');
    }

    private function pdfLogoDataUri(?GeneralSetting $generalSetting): ?string
    {
        if (! $generalSetting || ! $generalSetting->logo_light) {
            return null;
        }
        $path = public_path($generalSetting->logo_light);
        if (! is_readable($path)) {
            return null;
        }
        $data = @file_get_contents($path);
        if ($data === false) {
            return null;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };

        return 'data:'.$mime.';base64,'.base64_encode($data);
    }
}
