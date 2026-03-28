<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseReturnPaymentRequest;
use App\Http\Requests\StorePurchaseReturnRequest;
use App\Http\Requests\UpdatePurchaseReturnRequest;
use App\Models\GeneralSetting;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseReturnPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseReturnController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $purchaseId = $request->input('purchase_id');

        $returns = PurchaseReturn::query()
            ->with(['purchase.supplier', 'warehouse'])
            ->when($purchaseId, fn ($q) => $q->where('purchase_id', (int) $purchaseId))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('return_invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('purchase', function ($p) use ($search) {
                            $p->where('invoice_no', 'like', '%'.$search.'%')
                                ->orWhereHas('supplier', function ($s) use ($search) {
                                    $s->where('name', 'like', '%'.$search.'%')
                                        ->orWhere('phone', 'like', '%'.$search.'%');
                                });
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('purchase-returns.index', [
            'returns' => $returns,
            'filterPurchaseId' => $purchaseId,
        ]);
    }

    public function create(Purchase $purchase): View|RedirectResponse
    {
        $purchase->load(['items.product.unit', 'supplier', 'warehouse']);

        $lines = $purchase->items->map(function (PurchaseItem $line) {
            $rem = PurchaseReturn::remainingReturnableForPurchaseItem($line);

            return [
                'purchase_item_id' => $line->id,
                'product_name' => $line->product_name,
                'sku' => $line->sku,
                'purchase_quantity' => (float) $line->quantity,
                'stock_quantity' => $rem,
                'return_quantity' => 0,
                'unit_price' => (float) $line->unit_price,
                'unit_label' => $line->unit_label,
            ];
        })->filter(fn (array $l) => $l['stock_quantity'] > 0)->values()->all();

        if ($lines === []) {
            return redirect()->route('purchases.all')
                ->with('error', 'There is nothing left to return for this purchase.');
        }

        return view('purchase-returns.create', [
            'purchase' => $purchase,
            'lines' => $lines,
            'invoiceNo' => PurchaseReturn::nextInvoiceNo(),
            'currencyCode' => GeneralSetting::first()?->currency ?? 'USD',
        ]);
    }

    public function store(StorePurchaseReturnRequest $request, Purchase $purchase): RedirectResponse
    {
        $discount = round((float) $request->validated()['discount'], 2);
        $data = $this->buildTotalsFromItems(
            $purchase,
            $request->validated()['items'],
            $discount,
            null
        );

        if ($data['receivable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        DB::transaction(function () use ($request, $purchase, $data) {
            $ret = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'return_invoice_no' => PurchaseReturn::nextInvoiceNo(),
                'return_date' => $request->validated()['return_date'],
                'warehouse_id' => $purchase->warehouse_id,
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'],
                'receivable_amount' => $data['receivable'],
                'received_amount' => 0,
                'note' => $request->validated()['note'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $ret->id,
                    'purchase_item_id' => $line['purchase_item_id'],
                    'product_id' => $line['product_id'],
                    'product_name' => $line['product_name'],
                    'sku' => $line['sku'],
                    'purchase_quantity' => $line['purchase_quantity'],
                    'stock_quantity' => $line['stock_quantity'],
                    'return_quantity' => $line['return_quantity'],
                    'unit_label' => $line['unit_label'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);
            }
        });

        return redirect()->route('purchases.return')->with('success', 'Purchase return created successfully.');
    }

    public function edit(PurchaseReturn $purchaseReturn): View
    {
        $purchaseReturn->load(['items.purchaseItem', 'purchase.supplier', 'purchase.warehouse']);

        $lines = $purchaseReturn->items->map(function (PurchaseReturnItem $item) use ($purchaseReturn) {
            $pi = $item->purchaseItem;
            $rem = PurchaseReturn::remainingReturnableForPurchaseItem($pi, $purchaseReturn->id);

            return [
                'purchase_item_id' => $item->purchase_item_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'purchase_quantity' => (float) $item->purchase_quantity,
                'stock_quantity' => (float) $item->stock_quantity,
                'return_quantity' => (float) $item->return_quantity,
                'unit_price' => (float) $item->unit_price,
                'unit_label' => $item->unit_label,
                'max_return' => $rem + (float) $item->return_quantity,
            ];
        })->values()->all();

        return view('purchase-returns.edit', [
            'purchaseReturn' => $purchaseReturn,
            'lines' => $lines,
            'currencyCode' => GeneralSetting::first()?->currency ?? 'USD',
        ]);
    }

    public function update(UpdatePurchaseReturnRequest $request, PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $purchase = $purchaseReturn->purchase;
        $discount = round((float) $request->validated()['discount'], 2);
        $data = $this->buildTotalsFromItems(
            $purchase,
            $request->validated()['items'],
            $discount,
            $purchaseReturn->id
        );

        if ($data['receivable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        if ($data['receivable'] < (float) $purchaseReturn->received_amount - 0.0001) {
            return back()->withInput()->withErrors(['discount' => 'Receivable cannot be less than amount already received.']);
        }

        DB::transaction(function () use ($request, $purchaseReturn, $data) {
            $purchaseReturn->items()->delete();

            $purchaseReturn->update([
                'return_date' => $request->validated()['return_date'],
                'subtotal' => $data['subtotal'],
                'discount' => $data['discount'],
                'receivable_amount' => $data['receivable'],
                'note' => $request->validated()['note'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_item_id' => $line['purchase_item_id'],
                    'product_id' => $line['product_id'],
                    'product_name' => $line['product_name'],
                    'sku' => $line['sku'],
                    'purchase_quantity' => $line['purchase_quantity'],
                    'stock_quantity' => $line['stock_quantity'],
                    'return_quantity' => $line['return_quantity'],
                    'unit_label' => $line['unit_label'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);
            }
        });

        return redirect()->route('purchases.return')->with('success', 'Purchase return updated successfully.');
    }

    public function storePayment(StorePurchaseReturnPaymentRequest $request, PurchaseReturn $purchaseReturn): RedirectResponse
    {
        $due = $purchaseReturn->due_amount;
        $recv = (float) $request->validated()['receiving_amount'];
        if ($recv > $due + 0.0001) {
            return back()->withErrors(['receiving_amount' => 'Amount cannot exceed the due balance.']);
        }

        DB::transaction(function () use ($purchaseReturn, $recv, $request) {
            PurchaseReturnPayment::create([
                'purchase_return_id' => $purchaseReturn->id,
                'user_id' => $request->user()?->id,
                'amount' => $recv,
                'paid_at' => now(),
            ]);
            $purchaseReturn->increment('received_amount', $recv);
        });

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $returns = $this->filteredReturnsQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('purchase-returns.pdf.all', [
            'returns' => $returns,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('all-purchase-returns-'.time().'.pdf');
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $returns = $this->filteredReturnsQuery($request)->get();
        $filename = 'all-purchase-returns-'.time().'.csv';

        return response()->streamDownload(function () use ($returns) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, [
                'Invoice No.',
                'Date',
                'Supplier',
                'Mobile',
                'Total Amount',
                'Warehouse',
                'Discount',
                'Receivable',
                'Received',
                'Due',
            ]);
            foreach ($returns as $r) {
                fputcsv($out, [
                    $r->return_invoice_no,
                    $r->return_date->format('m/d/Y'),
                    $r->purchase->supplier->name,
                    $r->purchase->supplier->phone ?? '',
                    formatCurrency($r->subtotal),
                    $r->warehouse->name,
                    formatCurrency($r->discount),
                    formatCurrency($r->receivable_amount),
                    formatCurrency($r->received_amount),
                    formatCurrency($r->due_amount),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdfInvoice(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['items', 'purchase.supplier', 'warehouse']);
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('purchase-returns.pdf.invoice', [
            'purchaseReturn' => $purchaseReturn,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('purchase-return-'.$purchaseReturn->return_invoice_no.'.pdf');
    }

    /**
     * @param  array<int, array{purchase_item_id: int, return_quantity: float|int|string}>  $itemsInput
     * @return array{subtotal: float, discount: float, receivable: float, lines: list<array<string, mixed>>}
     */
    private function buildTotalsFromItems(Purchase $purchase, array $itemsInput, float $discount, ?int $excludeReturnId): array
    {
        $subtotal = 0;
        $lines = [];

        foreach ($itemsInput as $row) {
            $rq = (float) ($row['return_quantity'] ?? 0);
            if ($rq <= 0) {
                continue;
            }

            $line = PurchaseItem::where('purchase_id', $purchase->id)->findOrFail($row['purchase_item_id']);
            $unitPrice = (float) $line->unit_price;
            $lineTotal = round($rq * $unitPrice, 2);
            $subtotal += $lineTotal;

            $stockQty = PurchaseReturn::remainingReturnableForPurchaseItem($line, $excludeReturnId);

            $lines[] = [
                'purchase_item_id' => $line->id,
                'product_id' => $line->product_id,
                'product_name' => $line->product_name,
                'sku' => $line->sku,
                'purchase_quantity' => (float) $line->quantity,
                'stock_quantity' => $stockQty,
                'return_quantity' => $rq,
                'unit_label' => $line->unit_label,
                'unit_price' => $unitPrice,
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

    private function filteredReturnsQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $purchaseId = $request->input('purchase_id');

        return PurchaseReturn::query()
            ->with(['purchase.supplier', 'warehouse'])
            ->when($purchaseId, fn ($q) => $q->where('purchase_id', (int) $purchaseId))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('return_invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('purchase', function ($p) use ($search) {
                            $p->where('invoice_no', 'like', '%'.$search.'%')
                                ->orWhereHas('supplier', function ($s) use ($search) {
                                    $s->where('name', 'like', '%'.$search.'%')
                                        ->orWhere('phone', 'like', '%'.$search.'%');
                                });
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
            ->orderByDesc('return_date')
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
