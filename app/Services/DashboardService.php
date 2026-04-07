<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get summary statistics for the dashboard.
     */
    public function getSummaryStats(): array
    {
        return [
            'total_products' => Product::count(),
            'total_customers' => Customer::count(),
            'total_suppliers' => Supplier::count(),
            'total_categories' => Category::count(),
        ];
    }

    /**
     * Get transaction statistics for sales and purchases.
     */
    public function getTransactionStats(): array
    {
        return [
            'sales' => [
                'count' => Sale::count(),
                'total_amount' => (float) Sale::sum('receivable_amount'),
            ],
            'sale_returns' => [
                'count' => SaleReturn::count(),
                'total_amount' => (float) SaleReturn::sum('payable_amount'),
            ],
            'purchases' => [
                'count' => Purchase::count(),
                'total_amount' => (float) Purchase::sum('payable_amount'),
            ],
            'purchase_returns' => [
                'count' => PurchaseReturn::count(),
                'total_amount' => (float) PurchaseReturn::sum('receivable_amount'),
            ],
        ];
    }

    /**
     * Get recent transactions for the dashboard.
     */
    public function getRecentSales(int $limit = 5)
    {
        return Sale::with(['customer', 'warehouse'])
            ->orderBy('sale_date', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get expiring product batches for the dashboard.
     */
    public function getExpiringBatches(int $limit = 5)
    {
        $now = Carbon::now();
        $thirtyDaysFromNow = $now->clone()->addDays(30);

        return ProductBatch::with(['product', 'warehouse'])
            ->where('expiration_date', '<=', $thirtyDaysFromNow)
            ->where('expiration_date', '>=', $now)
            ->orderBy('expiration_date', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($batch) {
                $daysLeft = $batch->expiration_date->diffInDays(now(), false);
                $status = $daysLeft < 0 ? 'expired' : ($daysLeft < 5 ? 'urgent' : 'warning');
                return [
                    'batch' => $batch,
                    'days_left' => $daysLeft,
                    'status' => $status,
                ];
            });
    }
}
