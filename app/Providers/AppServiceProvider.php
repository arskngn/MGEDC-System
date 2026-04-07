<?php

namespace App\Providers;

use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Adjustment;
use App\Models\Transfer;
use App\Models\Expense;
use App\Models\PurchaseReturn;
use App\Models\SaleReturn;
use App\Models\SalePayment;
use App\Models\PurchasePayment;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\AdjustmentItem;
use App\Models\TransferItem;
use App\Observers\SaleReturnItemObserver;
use App\Observers\AdjustmentItemObserver;
use App\Observers\TransferItemObserver;
use App\Observers\GeneralObserver;
use App\Observers\StockItemObserver;
use App\Observers\PurchaseItemObserver;
use App\Observers\SaleItemObserver;
use App\Policies\SalePolicy;
use App\Policies\PurchasePolicy;
use App\Policies\CustomerPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();

        if (Schema::hasTable('general_settings')) {
            $generalSetting = GeneralSetting::first();
            View::share('generalSetting', $generalSetting);
        }

        // Register Policies
        Gate::policy(Sale::class, SalePolicy::class);
        Gate::policy(Purchase::class, PurchasePolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);

        // Register Observers
        Product::observe(GeneralObserver::class);
        Sale::observe(GeneralObserver::class);
        Purchase::observe(GeneralObserver::class);
        Customer::observe(GeneralObserver::class);
        Supplier::observe(GeneralObserver::class);
        Adjustment::observe(GeneralObserver::class);
        Transfer::observe(GeneralObserver::class);
        Expense::observe(GeneralObserver::class);
        PurchaseReturn::observe(GeneralObserver::class);
        SaleReturn::observe(GeneralObserver::class);
        SalePayment::observe(GeneralObserver::class);
        PurchasePayment::observe(GeneralObserver::class);

        // Register Product Batch Observer (for expiration tracking)
        PurchaseItem::observe(PurchaseItemObserver::class);

        // Register Stock Observers
        PurchaseItem::observe(StockItemObserver::class);
        PurchaseReturnItem::observe(StockItemObserver::class);
        SaleItem::observe(SaleItemObserver::class);
        SaleItem::observe(StockItemObserver::class);
        SaleReturnItem::observe(StockItemObserver::class);
        SaleReturnItem::observe(SaleReturnItemObserver::class);
        AdjustmentItem::observe(StockItemObserver::class);
        AdjustmentItem::observe(AdjustmentItemObserver::class);
        TransferItem::observe(StockItemObserver::class);
        TransferItem::observe(TransferItemObserver::class);
    }
}
