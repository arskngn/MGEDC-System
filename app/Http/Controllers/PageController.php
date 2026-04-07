<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\SalePayment;
use App\Models\PurchasePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PageController extends Controller
{
    public function salesAll()
    {
        return view('pages.empty', ['title' => 'All Sales']);
    }

    public function salesReturn(Request $request)
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $salesReturns = \App\Models\SaleReturn::query()
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
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('sales.return-index', ['salesReturns' => $salesReturns]);
    }

    public function customers()
    {
        return view('pages.empty', ['title' => 'Manage Customers']);
    }

    public function suppliers()
    {
        return view('pages.empty', ['title' => 'Manage Suppliers']);
    }

    public function staffAll()
    {
        return view('pages.empty', ['title' => 'All Staff']);
    }

    public function staffRoles()
    {
        return view('pages.empty', ['title' => 'Roles']);
    }

    public function transfers()
    {
        return view('pages.empty', ['title' => 'Transfers']);
    }

    public function expenseTypes()
    {
        return view('pages.empty', ['title' => 'Expense Types']);
    }

    public function expenses()
    {
        return view('pages.empty', ['title' => 'Expenses']);
    }

    public function reportSupplierPayments(Request $request)
    {
        // Redirect to the new supplier payments page
        return redirect()->route('supplier-payments.index', $request->query());
    }

    public function reportCustomerPayments(Request $request)
    {
        // Redirect to the new customer payments page
        return redirect()->route('customer-payments.index', $request->query());
    }

    public function reportStock(Request $request)
    {
        $filterBy = $request->string('filter_by')->trim()->value() ?: 'warehouse';
        $warehouseId = $request->integer('warehouse_id') ?: null;
        $productId = $request->integer('product_id') ?: null;

        $warehouses = \App\Models\Warehouse::query()
            ->enabled()
            ->orderBy('name')
            ->get();

        $stockData = collect();

        if ($filterBy === 'warehouse' && $warehouseId) {
            // Get all products and their stock in the selected warehouse
            $products = \App\Models\Product::query()
                ->orderBy('name')
                ->get();

            foreach ($products as $product) {
                $stock = $this->getProductStockForWarehouse($product->id, $warehouseId);
                
                // Get expiration batch info
                $batches = \App\Models\ProductBatch::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->orderBy('expiration_date')
                    ->get();
                
                $expirationStatus = $this->getExpirationStatus($batches);
                
                $stockData->push([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'warehouse_id' => $warehouseId,
                    'warehouse_name' => $warehouses->firstWhere('id', $warehouseId)?->name,
                    'stock_quantity' => $stock,
                    'alert_quantity' => $product->alert_quantity,
                    'unit' => $product->unit?->short_name ?? 'Unit',
                    'is_low_stock' => $stock <= $product->alert_quantity,
                    'expiration_status' => $expirationStatus['status'],
                    'earliest_expiration' => $expirationStatus['earliest_date'],
                    'batches_count' => $batches->count(),
                ]);
            }
        } elseif ($filterBy === 'product' && $productId) {
            // Get the product's stock across all warehouses
            $product = \App\Models\Product::find($productId);
            if ($product) {
                foreach ($warehouses as $warehouse) {
                    $stock = $this->getProductStockForWarehouse($productId, $warehouse->id);
                    
                    // Get expiration batch info
                    $batches = \App\Models\ProductBatch::where('product_id', $productId)
                        ->where('warehouse_id', $warehouse->id)
                        ->orderBy('expiration_date')
                        ->get();
                    
                    $expirationStatus = $this->getExpirationStatus($batches);
                    
                    $stockData->push([
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'warehouse_id' => $warehouse->id,
                        'warehouse_name' => $warehouse->name,
                        'stock_quantity' => $stock,
                        'alert_quantity' => $product->alert_quantity,
                        'unit' => $product->unit?->short_name ?? 'Unit',
                        'is_low_stock' => $stock <= $product->alert_quantity,
                        'expiration_status' => $expirationStatus['status'],
                        'earliest_expiration' => $expirationStatus['earliest_date'],
                        'batches_count' => $batches->count(),
                    ]);
                }
            }
        }

        $products = \App\Models\Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);

        return view('pages.stock-report', [
            'title' => 'Stock Report',
            'filterBy' => $filterBy,
            'warehouseId' => $warehouseId,
            'productId' => $productId,
            'warehouses' => $warehouses,
            'products' => $products,
            'stockData' => $stockData,
        ]);
    }

    private function getProductStockForWarehouse(int $productId, int $warehouseId): float
    {
        $purchases = \App\Models\PurchaseItem::whereHas('purchase', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        })->where('product_id', $productId)->sum('quantity');

        $purchaseReturns = \App\Models\PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        })->where('product_id', $productId)->sum('return_quantity');

        $sales = \App\Models\SaleItem::whereHas('sale', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        })->where('product_id', $productId)->sum('quantity');

        $saleReturns = \App\Models\SaleReturnItem::whereHas('saleReturn', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        })->where('product_id', $productId)->sum('return_quantity');

        $transfersIn = \App\Models\TransferItem::whereHas('transfer', function ($q) use ($warehouseId) {
            $q->where('to_warehouse_id', $warehouseId);
        })->where('product_id', $productId)->sum('quantity');

        $transfersOut = \App\Models\TransferItem::whereHas('transfer', function ($q) use ($warehouseId) {
            $q->where('from_warehouse_id', $warehouseId);
        })->where('product_id', $productId)->sum('quantity');

        // Calculate adjustments by type separately to avoid MySQL GROUP BY issues
        $adjustmentsAdd = \App\Models\AdjustmentItem::whereHas('adjustment', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        })->where('product_id', $productId)
            ->where('type', 'Add')
            ->sum('quantity') ?? 0;

        $adjustmentsSubtract = \App\Models\AdjustmentItem::whereHas('adjustment', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        })->where('product_id', $productId)
            ->where('type', 'Remove')
            ->sum('quantity') ?? 0;

        $adjustments = $adjustmentsAdd - $adjustmentsSubtract;

        return (float) ($purchases - $purchaseReturns - $sales + $saleReturns + $transfersIn - $transfersOut + $adjustments);
    }

    private function getExpirationStatus($batches)
    {
        if ($batches->isEmpty()) {
            return ['status' => 'normal', 'earliest_date' => null];
        }

        $now = \Carbon\Carbon::now();
        $earliest = $batches[0]->expiration_date;
        $daysUntilExpiration = $now->diffInDays($earliest, false);

        if ($daysUntilExpiration < 0) {
            return ['status' => 'expired', 'earliest_date' => $earliest];
        } elseif ($daysUntilExpiration < 5) {
            return ['status' => 'urgent', 'earliest_date' => $earliest];
        } elseif ($daysUntilExpiration < 30) {
            return ['status' => 'warning', 'earliest_date' => $earliest];
        } else {
            return ['status' => 'normal', 'earliest_date' => $earliest];
        }
    }

    public function exportStock(Request $request)
    {
        $format = $request->string('format')->value() ?: 'pdf';
        
        // Check permission based on format
        if ($format === 'pdf' && !$request->user()->hasPermission('Download Stock Report PDF')) {
            abort(403, 'Unauthorized action.');
        }
        if ($format === 'csv' && !$request->user()->hasPermission('Download Stock Report CSV')) {
            abort(403, 'Unauthorized action.');
        }

        $filterBy = $request->string('filter_by')->trim()->value() ?: 'warehouse';
        $warehouseId = $request->integer('warehouse_id') ?: null;
        $productId = $request->integer('product_id') ?: null;

        $warehouses = \App\Models\Warehouse::query()
            ->enabled()
            ->orderBy('name')
            ->get();

        $stockData = collect();

        if ($filterBy === 'warehouse' && $warehouseId) {
            $products = \App\Models\Product::query()
                ->orderBy('name')
                ->get();

            foreach ($products as $product) {
                $stock = $this->getProductStockForWarehouse($product->id, $warehouseId);
                $stockData->push([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'warehouse_id' => $warehouseId,
                    'warehouse_name' => $warehouses->firstWhere('id', $warehouseId)?->name,
                    'stock_quantity' => $stock,
                    'alert_quantity' => $product->alert_quantity,
                    'unit' => $product->unit?->short_name ?? 'Unit',
                    'is_low_stock' => $stock <= $product->alert_quantity,
                ]);
            }
        } elseif ($filterBy === 'product' && $productId) {
            $product = \App\Models\Product::find($productId);
            if ($product) {
                foreach ($warehouses as $warehouse) {
                    $stock = $this->getProductStockForWarehouse($productId, $warehouse->id);
                    $stockData->push([
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'warehouse_id' => $warehouse->id,
                        'warehouse_name' => $warehouse->name,
                        'stock_quantity' => $stock,
                        'alert_quantity' => $product->alert_quantity,
                        'unit' => $product->unit?->short_name ?? 'Unit',
                        'is_low_stock' => $stock <= $product->alert_quantity,
                    ]);
                }
            }
        }

        if ($format === 'csv') {
            return $this->exportStockCsv($stockData, $filterBy);
        }

        return $this->exportStockPdf($stockData, $filterBy, $warehouseId, $productId);
    }

    private function exportStockCsv($stockData, $filterBy)
    {
        $filename = 'stock-report-' . date('Y-m-d-H-i-s') . '.csv';

        return response()->streamDownload(function () use ($stockData, $filterBy) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($filterBy === 'warehouse') {
                fputcsv($out, ['S.N.', 'Product Name', 'SKU', 'Category', 'Brand', 'Stock', 'Unit']);
                foreach ($stockData as $index => $item) {
                    $product = \App\Models\Product::find($item['product_id']);
                    fputcsv($out, [
                        $index + 1,
                        $item['product_name'],
                        $item['sku'],
                        $product?->category?->name ?? '-',
                        $product?->brand?->name ?? '-',
                        $item['stock_quantity'],
                        $item['unit']
                    ]);
                }
            } else {
                fputcsv($out, ['S.N.', 'Warehouse', 'Current Stock', 'Unit']);
                foreach ($stockData as $index => $item) {
                    fputcsv($out, [
                        $index + 1,
                        $item['warehouse_name'],
                        $item['stock_quantity'],
                        $item['unit']
                    ]);
                }
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function exportStockPdf($stockData, $filterBy, $warehouseId, $productId)
    {
        $generalSetting = GeneralSetting::first();
        $warehouse = $warehouseId ? \App\Models\Warehouse::find($warehouseId) : null;
        $product = $productId ? \App\Models\Product::find($productId) : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.stock-report-pdf', [
            'stockData' => $stockData,
            'filterBy' => $filterBy,
            'warehouse' => $warehouse,
            'product' => $product,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('stock-report-' . date('Y-m-d-H-i-s') . '.pdf');
    }

    private function pdfLogoDataUri(?GeneralSetting $generalSetting): ?string
    {
        if (!$generalSetting || !$generalSetting->logo_light) {
            return null;
        }
        $path = public_path($generalSetting->logo_light);
        if (!is_readable($path)) {
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

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }

    public function reportDataEntryPurchases()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Purchase::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Purchase::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Purchases',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'purchase'
        ]);
    }

    public function reportDataEntryPurchaseReturn()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\PurchaseReturn::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\PurchaseReturn::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Purchase Return',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'purchase-return'
        ]);
    }

    public function reportDataEntrySales()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Sale::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Sale::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Sales',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'sale'
        ]);
    }

    public function reportDataEntrySaleReturn()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\SaleReturn::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\SaleReturn::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Sale Return',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'sale-return'
        ]);
    }

    public function reportDataEntryProducts()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Product::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Product::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Products',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'product'
        ]);
    }

    public function reportDataEntryCustomers()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Customer::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Customer::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Customers',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'customer'
        ]);
    }

    public function reportDataEntryCustomerPayments()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', SalePayment::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = SalePayment::with('sale.customer')->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Customer Payments',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'customer-payment'
        ]);
    }

    public function reportDataEntrySuppliers()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Supplier::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Supplier::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Suppliers',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'supplier'
        ]);
    }

    public function reportDataEntrySupplierPayments()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', PurchasePayment::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = PurchasePayment::with('purchase.supplier')->whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Supplier Payments',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'supplier-payment'
        ]);
    }

    public function reportDataEntryAdjustments()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Adjustment::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Adjustment::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Adjustments',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'adjustment'
        ]);
    }

    public function reportDataEntryTransfers()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Transfer::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Transfer::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Transfers',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'transfer'
        ]);
    }

    public function reportDataEntryExpenses()
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 20;
        $activities = \App\Models\ActivityLog::query()
            ->where('model_type', \App\Models\Expense::class)
            ->with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $modelIds = $activities->pluck('model_id');
        $models = \App\Models\Expense::whereIn('id', $modelIds)->get()->keyBy('id');

        return view('pages.data-entry-report', [
            'title' => 'Data Entry: Expenses',
            'activities' => $activities,
            'models' => $models,
            'reportType' => 'expense'
        ]);
    }

    public function extraApplication()
    {
        $generalSetting = GeneralSetting::first();
        $data = [
            'title' => 'Application Information',
            'app_name' => $generalSetting->site_title ?? 'MGEDC',
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            'timezone' => $generalSetting->timezone ?? 'UTC',
        ];

        return view('pages.extra.application', $data);
    }

    public function extraServer()
    {
        // Detect server software
        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? null;
        if (! $server_software) {
            // Try to detect from common server software headers
            $server_software = $_SERVER['HTTP_USER_AGENT'] ?? 'Not Detected';
        }

        // PHP SAPI (Server API) - shows if CLI, FPM, Apache, etc.
        $php_sapi = php_sapi_name();

        // Operating System
        $os = php_uname('s');

        // Server Address/IP
        $server_ip = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()) ?? 'Not Detected';

        // HTTP Protocol
        $server_protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'Not Detected';

        // HTTP Host
        $http_host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'Not Detected');

        // Server Port
        $server_port = $_SERVER['SERVER_PORT'] ?? 'Not Detected';

        $data = [
            'title' => 'Server Information',
            'php_version' => phpversion(),
            'php_sapi' => $php_sapi,
            'server_software' => $server_software,
            'operating_system' => $os,
            'server_ip' => $server_ip,
            'server_protocol' => $server_protocol,
            'http_host' => $http_host,
            'server_port' => $server_port,
        ];

        return view('pages.extra.server', $data);
    }

    public function extraCache()
    {
        return view('pages.extra.cache', ['title' => 'Clear System Cache']);
    }

    public function clearCache(Request $request)
    {
        try {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            Artisan::call('optimize:clear');

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'All caches cleared successfully!']);
            }

            return back()->with('success', 'All caches cleared successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error clearing cache: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Error clearing cache: '.$e->getMessage());
        }
    }

    public function extraUpdate()
    {
        return view('pages.extra.update', ['title' => 'System Updates']);
    }

    public function reportRequest()
    {
        return view('pages.empty', ['title' => 'Report & Request']);
    }
}
