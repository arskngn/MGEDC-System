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
use App\Services\SaleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

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
        $totals = $this->saleService->calculateTotals($request->validated()['items'], (float) $request->validated()['discount']);
        
        if ($totals['receivable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        $this->saleService->createSale($request->validated());

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

        $totals = $this->saleService->calculateTotals($request->validated()['items'], (float) $request->validated()['discount']);
        
        if ($totals['receivable'] < 0) {
            return back()->withInput()->withErrors(['discount' => 'Discount cannot exceed subtotal.']);
        }

        if ($totals['receivable'] < (float) $sale->paid_amount - 0.0001) {
            return back()->withInput()->withErrors(['discount' => 'Receivable cannot be less than amount already received.']);
        }

        $this->saleService->updateSale($sale, $request->validated());

        return redirect()->route('sales.all')->with('success', 'Sale updated successfully.');
    }

    public function storePayment(StoreSalePaymentRequest $request, Sale $sale): RedirectResponse
    {
        $due = $sale->due_amount;
        $receiving = (float) $request->validated()['receiving_amount'];
        if ($receiving > $due + 0.0001) {
            return back()->withErrors(['receiving_amount' => 'Amount cannot exceed the due balance.']);
        }

        $this->saleService->receivePayment($sale, $receiving, $request->user()?->id);

        return back()->with('success', 'Payment received successfully.');
    }

    public function searchProducts(Request $request)
    {
        $q = $request->string('q')->trim()->value();

        $products = Product::query()
            ->with('unit')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%' . $q . '%')
                        ->orWhere('sku', 'like', '%' . $q . '%');
                });
            })
            ->when($q === '', function ($query) {
                $query->orderByDesc('id');
            }, function ($query) {
                $query->orderBy('name');
            })
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
            'restocking_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $sale->load(['items', 'customer', 'warehouse']);

        $returnItems = collect($request->input('return_items'))->filter(fn ($item) => (float) $item['return_quantity'] > 0);

        if ($returnItems->isEmpty()) {
            return back()->withInput()->withErrors(['return_items' => 'At least one item must have a return quantity greater than zero.']);
        }

        $this->saleService->storeReturn(
            $sale,
            $returnItems->toArray(),
            (float) $request->input('discount', 0),
            $request->input('note'),
            (float) $request->input('restocking_fee', 0)
        );

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
