<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAdjustmentRequest;
use App\Http\Requests\UpdateAdjustmentRequest;
use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\AdjustmentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdjustmentController extends Controller
{
    public function __construct(
        protected AdjustmentService $adjustmentService
    ) {}

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $adjustments = Adjustment::query()
            ->with(['warehouse', 'items'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where('tracking_no', 'like', '%'.$search.'%');
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('adjustment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('adjustment_date', '<=', $dateTo))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('adjustments.index', [
            'adjustments' => $adjustments,
        ]);
    }

    public function create(): View
    {
        return view('adjustments.create', [
            'trackingNo' => Adjustment::nextTrackingNo(),
            'warehouses' => Warehouse::query()->enabled()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreAdjustmentRequest $request): RedirectResponse
    {
        $this->adjustmentService->createAdjustment($request->validated());

        return redirect()->route('adjustments.index')->with('success', 'Adjustment created successfully.');
    }

    public function edit(Adjustment $adjustment): View
    {
        $adjustment->load(['warehouse', 'items.product.unit']);

        return view('adjustments.edit', [
            'adjustment' => $adjustment,
            'warehouses' => Warehouse::query()->enabled()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateAdjustmentRequest $request, Adjustment $adjustment): RedirectResponse
    {
        $this->adjustmentService->updateAdjustment($adjustment, $request->validated());

        return redirect()->route('adjustments.index')->with('success', 'Adjustment updated successfully.');
    }

    public function destroy(Adjustment $adjustment): RedirectResponse
    {
        $this->adjustmentService->deleteAdjustment($adjustment);

        return redirect()->route('adjustments.index')->with('success', 'Adjustment deleted successfully.');
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

    public function exportPdfInvoice(Adjustment $adjustment)
    {
        $adjustment->load(['warehouse', 'items.product.unit']);
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('adjustments.pdf.invoice', [
            'adjustment' => $adjustment,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('adjustment-'.$adjustment->tracking_no.'.pdf');
    }

    public function exportPdfAll(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $adjustments = Adjustment::query()
            ->with(['warehouse', 'items'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('tracking_no', 'like', '%'.$search.'%')
                        ->orWhereHas('warehouse', function ($w) use ($search) {
                            $w->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('adjustment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('adjustment_date', '<=', $dateTo))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->get();

        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('adjustments.pdf.all', [
            'adjustments' => $adjustments,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('all-adjustments-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function exportCsvAll(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $adjustments = Adjustment::query()
            ->with(['warehouse', 'items'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('tracking_no', 'like', '%'.$search.'%')
                        ->orWhereHas('warehouse', function ($w) use ($search) {
                            $w->where('name', 'like', '%'.$search.'%');
                        });
            });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('adjustment_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('adjustment_date', '<=', $dateTo))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->get();

        $filename = 'adjustments-'.now()->format('Y-m-d-His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($adjustments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S.N.', 'Tracking No.', 'Date', 'Warehouse', 'Products', 'Note']);

            foreach ($adjustments as $i => $adjustment) {
                fputcsv($file, [
                    $i + 1,
                    $adjustment->tracking_no,
                    $adjustment->adjustment_date->format('d M, Y'),
                    $adjustment->warehouse->name,
                    count($adjustment->items),
                    $adjustment->note ?? '',
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
