<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Warehouse;
use App\Services\TransferService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function __construct(
        protected TransferService $transferService
    ) {}

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $transfers = Transfer::query()
            ->with(['fromWarehouse', 'toWarehouse', 'items'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('tracking_no', 'like', '%'.$search.'%');
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('transfer_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('transfer_date', '<=', $dateTo))
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('transfers.index', [
            'transfers' => $transfers,
        ]);
    }

    public function create(): View
    {
        return view('transfers.create', [
            'trackingNo' => Transfer::nextTrackingNo(),
            'warehouses' => Warehouse::query()->enabled()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_no' => 'required|string',
            'from_warehouse_id' => 'required|integer|exists:warehouses,id',
            'to_warehouse_id' => 'required|integer|exists:warehouses,id|different:from_warehouse_id',
            'transfer_date' => 'required|date',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.product_batch_id' => 'required|integer|exists:product_batches,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $this->transferService->createTransfer($validated);

        return redirect()->route('transfers.index')->with('success', 'Transfer created successfully.');
    }

    public function searchProducts(Request $request)
    {
        $q = $request->string('q')->trim()->value();
        $warehouseId = $request->integer('warehouse_id');

        $products = Product::query()
            ->with(['unit', 'batches' => function ($query) use ($warehouseId) {
                $query->where('warehouse_id', $warehouseId)
                    ->whereIn('status', ['active', 'expiring'])
                    ->whereRaw('quantity > quantity_sold');
            }])
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', '%'.$q.'%')
                    ->orWhere('sku', 'like', '%'.$q.'%');
            })
            ->orderBy('name')
            ->limit(25)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'unit_label' => $p->unit->short_name ?? $p->unit->name,
                    'in_stock' => $p->current_stock,
                    'batches' => $p->batches->map(function ($b) {
                        return [
                            'id' => $b->id,
                            'batch_number' => $b->batch_number,
                            'expiration_date' => $b->expiration_date->format('Y-m-d'),
                            'available' => $b->quantity - $b->quantity_sold,
                        ];
                    }),
                ];
            });

        return response()->json($products);
    }

    public function edit(Transfer $transfer): View
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'items.product.unit']);

        return view('transfers.edit', [
            'transfer' => $transfer,
            'warehouses' => Warehouse::query()->enabled()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Transfer $transfer): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_no' => 'required|string',
            'from_warehouse_id' => 'required|integer|exists:warehouses,id',
            'to_warehouse_id' => 'required|integer|exists:warehouses,id|different:from_warehouse_id',
            'transfer_date' => 'required|date',
            'note' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $this->transferService->updateTransfer($transfer, $validated);

        return redirect()->route('transfers.index')->with('success', 'Transfer updated successfully.');
    }

    public function destroy(Transfer $transfer): RedirectResponse
    {
        $this->transferService->deleteTransfer($transfer);

        return redirect()->route('transfers.index')->with('success', 'Transfer deleted successfully.');
    }

    public function exportPdfInvoice(Transfer $transfer)
    {
        $transfer->load(['fromWarehouse', 'toWarehouse', 'items.product.unit']);
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('transfers.pdf.invoice', [
            'transfer' => $transfer,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('transfer-'.$transfer->tracking_no.'.pdf');
    }

    public function exportPdfAll(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $transfers = Transfer::query()
            ->with(['fromWarehouse', 'toWarehouse', 'items'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('tracking_no', 'like', '%'.$search.'%')
                        ->orWhereHas('fromWarehouse', function ($w) use ($search) {
                            $w->where('name', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('toWarehouse', function ($w) use ($search) {
                            $w->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('transfer_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('transfer_date', '<=', $dateTo))
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->get();

        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('transfers.pdf.all', [
            'transfers' => $transfers,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('all-transfers-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function exportCsvAll(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $transfers = Transfer::query()
            ->with(['fromWarehouse', 'toWarehouse', 'items'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('tracking_no', 'like', '%'.$search.'%')
                        ->orWhereHas('fromWarehouse', function ($w) use ($search) {
                            $w->where('name', 'like', '%'.$search.'%');
                        })
                        ->orWhereHas('toWarehouse', function ($w) use ($search) {
                            $w->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('transfer_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('transfer_date', '<=', $dateTo))
            ->orderByDesc('transfer_date')
            ->orderByDesc('id')
            ->get();

        $filename = 'transfers-'.now()->format('Y-m-d-His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($transfers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S.N.', 'Tracking No.', 'Date', 'From', 'To', 'Products', 'Note']);

            foreach ($transfers as $i => $transfer) {
                fputcsv($file, [
                    $i + 1,
                    $transfer->tracking_no,
                    $transfer->transfer_date->format('d M, Y'),
                    $transfer->fromWarehouse->name,
                    $transfer->toWarehouse->name,
                    count($transfer->items),
                    $transfer->note ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($data);
    }
}
