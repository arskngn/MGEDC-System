<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSalePaymentRequest;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Customer;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\SaleReturnPayment;
use App\Models\Warehouse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $sales = Sale::query()
            ->with(['customer', 'warehouse'])
            ->withCount('saleReturns')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('sale_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('sale_date', '<=', $dateTo))
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('sales.index', [
            'sales' => $sales,
        ]);
    }

    public function create(): View
    {
        return view('sales.create', [
            'invoiceNo' => Sale::nextInvoiceNo(),
            'customers' => Customer::orderBy('name')->get(),
            'warehouses' => Warehouse::query()->enabled()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $data = $this->buildTotalsFromItems($request->validated()['items'], (float) $request->validated()['discount']);
        if ($data['receivable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        DB::transaction(function () use ($request, $data) {
            $sale = Sale::create([
                'invoice_no' => $request->validated()['invoice_no'],
                'customer_id' => $request->validated()['customer_id'],
                'warehouse_id' => $request->validated()['warehouse_id'],
                'sale_date' => $request->validated()['sale_date'],
                'note' => $request->validated()['note'] ?? null,
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'],
                'receivable_amount' => $data['receivable'],
                'paid_amount' => 0,
            ]);

            foreach ($data['lines'] as $line) {
                SaleItem::create([
                    'sale_id' => $sale->id,
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

        return redirect()->route('sales.all')->with('success', 'Sale created successfully.');
    }

    public function edit(Sale $sale): View
    {
        $sale->load(['items.product.unit', 'customer', 'warehouse']);

        return view('sales.edit', [
            'sale' => $sale,
            'customers' => Customer::orderBy('name')->get(),
            'warehouses' => Warehouse::query()
                ->where(function ($q) use ($sale) {
                    $q->where('status', true)
                        ->orWhere('id', $sale->warehouse_id);
                })
                ->orderBy('name')
                ->get(),
            'hasReturns' => $sale->hasReturns(),
        ]);
    }

    public function update(UpdateSaleRequest $request, Sale $sale): RedirectResponse
    {
        if ($sale->hasReturns()) {
            return back()->with('error', 'You cannot edit a sale after a return has been recorded.');
        }

        $data = $this->buildTotalsFromItems($request->validated()['items'], (float) $request->validated()['discount']);
        if ($data['receivable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        if ($data['receivable'] < (float) $sale->paid_amount - 0.0001) {
            return back()->withInput()->withErrors(['discount' => 'Receivable cannot be less than amount already received.']);
        }

        DB::transaction(function () use ($request, $sale, $data) {
            $sale->items()->delete();

            $sale->update([
                'invoice_no' => $request->validated()['invoice_no'],
                'customer_id' => $request->validated()['customer_id'],
                'warehouse_id' => $request->validated()['warehouse_id'],
                'sale_date' => $request->validated()['sale_date'],
                'note' => $request->validated()['note'] ?? null,
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'],
                'receivable_amount' => $data['receivable'],
            ]);

            foreach ($data['lines'] as $line) {
                SaleItem::create([
                    'sale_id' => $sale->id,
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

        return redirect()->route('sales.all')->with('success', 'Sale updated successfully.');
    }

    public function storePayment(StoreSalePaymentRequest $request, Sale $sale): RedirectResponse
    {
        $due = $sale->due_amount;
        $receiving = (float) $request->validated()['receiving_amount'];
        if ($receiving > $due + 0.0001) {
            return back()->withErrors(['receiving_amount' => 'Amount cannot exceed the due balance.']);
        }

        DB::transaction(function () use ($sale, $receiving, $request) {
            SalePayment::create([
                'sale_id' => $sale->id,
                'user_id' => $request->user()?->id,
                'amount' => $receiving,
            ]);
            $sale->increment('paid_amount', $receiving);
        });

        return back()->with('success', 'Payment received successfully.');
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
                'default_sale_price' => (float) $p->default_sale_price,
                'in_stock' => $p->current_stock,
            ]);

        return response()->json($products);
    }

    public function returnCreate(Sale $sale): View
    {
        $sale->load(['customer', 'warehouse', 'items.product.unit']);

        return view('sales.return-create', [
            'sale' => $sale,
        ]);
    }

    public function returnStore(Request $request, Sale $sale): RedirectResponse
    {
        $request->validate([
            'return_items' => ['required', 'array', 'min:1'],
            'return_items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'return_items.*.return_quantity' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'discount' => ['required', 'numeric', 'min:0'],
        ]);

        $sale->load(['items', 'customer', 'warehouse']);

        $returnItems = collect($request->input('return_items'))->filter(fn ($item) => (float) $item['return_quantity'] > 0);

        if ($returnItems->isEmpty()) {
            return back()->withInput()->withErrors(['return_items' => 'At least one item must have a return quantity greater than zero.']);
        }

        $subtotal = 0;
        $lines = [];

        foreach ($returnItems as $item) {
            $saleItem = $sale->items->firstWhere('id', $item['sale_item_id']);
            if (! $saleItem) {
                continue;
            }

            $returnQty = (float) $item['return_quantity'];
            $lineTotal = round($returnQty * (float) $saleItem->unit_price, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'sale_item_id' => $saleItem->id,
                'product_id' => $saleItem->product_id,
                'product_name' => $saleItem->product_name,
                'sku' => $saleItem->sku,
                'sale_quantity' => $saleItem->quantity,
                'return_quantity' => $returnQty,
                'unit_label' => $saleItem->unit_label,
                'unit_price' => $saleItem->unit_price,
                'line_total' => $lineTotal,
            ];
        }

        $discount = round((float) $request->input('discount', 0), 2);
        $payable = round(max(0, $subtotal - $discount), 2);

        // Generate return invoice number
        $maxReturnNo = \App\Models\SaleReturn::query()
            ->where('return_invoice_no', 'like', 'SR-%')
            ->get()
            ->map(fn ($r) => (int) preg_replace('/\D/', '', substr($r->return_invoice_no, 3)))
            ->max() ?? 0;
        $returnInvoiceNo = 'SR-'.str_pad((string) ($maxReturnNo + 1), 6, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($sale, $lines, $subtotal, $discount, $payable, $returnInvoiceNo, $request) {
            $saleReturn = \App\Models\SaleReturn::create([
                'sale_id' => $sale->id,
                'return_invoice_no' => $returnInvoiceNo,
                'return_date' => now()->toDateString(),
                'warehouse_id' => $sale->warehouse_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'payable_amount' => $payable,
                'paid_amount' => 0,
                'note' => $request->input('note'),
            ]);

            foreach ($lines as $line) {
                \App\Models\SaleReturnItem::create(array_merge($line, [
                    'sale_return_id' => $saleReturn->id,
                ]));
            }
        });

        return redirect()->route('sales.all')->with('success', 'Sale return recorded successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $sales = $this->filteredSalesQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('sales.pdf.all', [
            'sales' => $sales,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('all-sales-'.time().'.pdf');
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $sales = $this->filteredSalesQuery($request)->get();
        $filename = 'all-sales-'.time().'.csv';

        return response()->streamDownload(function () use ($sales) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Invoice No.',
                'Date',
                'Customer',
                'Mobile',
                'Warehouse',
                'Total Amount',
                'Discount',
                'Receivable',
                'Received',
                'Due',
            ]);
            foreach ($sales as $s) {
                fputcsv($out, [
                    $s->invoice_no,
                    $s->sale_date->format('m/d/Y'),
                    $s->customer->name,
                    $s->customer->phone ?? '',
                    $s->warehouse->name,
                    formatCurrency($s->subtotal),
                    formatCurrency($s->discount),
                    formatCurrency($s->receivable_amount),
                    formatCurrency($s->paid_amount),
                    formatCurrency($s->due_amount),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdfInvoice(Sale $sale)
    {
        $sale->load(['customer', 'warehouse', 'items.product.unit']);
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('sales.pdf.invoice', [
            'sale' => $sale,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('sale-'.$sale->invoice_no.'.pdf');
    }

    /**
     * @param  array<int, array{product_id: int, quantity: float|int|string, unit_price: float|int|string}>  $items
     * @return array{subtotal: float, discount: float, receivable: float, lines: list<array<string, mixed>>}
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
        $receivable = round(max(0, $subtotal - $discount), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'receivable' => $receivable,
            'lines' => $lines,
        ];
    }

    private function filteredSalesQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        return Sale::query()
            ->with(['customer', 'warehouse'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('sale_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('sale_date', '<=', $dateTo))
            ->orderByDesc('sale_date')
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

    public function exportPdfAllReturns(Request $request)
    {
        $salesReturns = $this->filteredSalesReturnsQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('sales.pdf.all-returns', [
            'salesReturns' => $salesReturns,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('all-sales-returns-'.time().'.pdf');
    }

    public function exportCsvAllReturns(Request $request): StreamedResponse
    {
        $salesReturns = $this->filteredSalesReturnsQuery($request)->get();
        $filename = 'all-sales-returns-'.time().'.csv';

        return response()->streamDownload(function () use ($salesReturns) {
            $out = fopen('php://output', 'w');
            // Write UTF-8 BOM
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Return Invoice No.',
                'Date',
                'Customer',
                'Mobile',
                'Warehouse',
                'Total Amount',
                'Discount',
                'Payable',
                'Paid',
                'Due',
            ]);
            foreach ($salesReturns as $sr) {
                fputcsv($out, [
                    $sr->return_invoice_no,
                    $sr->return_date->format('m/d/Y'),
                    $sr->sale->customer->name,
                    $sr->sale->customer->phone ?? '',
                    $sr->warehouse->name,
                    formatCurrency($sr->subtotal),
                    formatCurrency($sr->discount),
                    formatCurrency($sr->payable_amount),
                    formatCurrency($sr->paid_amount),
                    formatCurrency($sr->due_amount),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredSalesReturnsQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        return \App\Models\SaleReturn::query()
            ->with(['sale.customer', 'warehouse'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('return_invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('sale.customer', function ($c) use ($search) {
                            $c->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
            ->orderByDesc('return_date')
            ->orderByDesc('id');
    }

    public function storeReturnPayment(Request $request, SaleReturn $saleReturn): RedirectResponse
    {
        $request->validate([
            'paying_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $due = $saleReturn->due_amount;
        $paying = (float) $request->validated()['paying_amount'];
        if ($paying > $due + 0.0001) {
            return back()->withErrors(['paying_amount' => 'Amount cannot exceed the due balance.']);
        }

        DB::transaction(function () use ($saleReturn, $paying, $request) {
            SaleReturnPayment::create([
                'sale_return_id' => $saleReturn->id,
                'user_id' => $request->user()?->id,
                'amount' => $paying,
            ]);
            $saleReturn->increment('paid_amount', $paying);
        });

        return back()->with('success', 'Payment received successfully.');
    }

    public function exportPdfReturn(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale.customer', 'warehouse', 'items.product.unit']);
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('sales.pdf.return-invoice', [
            'saleReturn' => $saleReturn,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('return-'.$saleReturn->return_invoice_no.'.pdf');
    }
}
