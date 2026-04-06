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
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\AdjustmentItem;
use App\Observers\GeneralObserver;
use App\Observers\StockItemObserver;
use App\Policies\SalePolicy;
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

        // Register Observers
        Product::observe(GeneralObserver::class);
        Sale::observe(GeneralObserver::class);
        Purchase::observe(GeneralObserver::class);
        Customer::observe(GeneralObserver::class);
        Supplier::observe(GeneralObserver::class);
        Adjustment::observe(GeneralObserver::class);
        Transfer::observe(GeneralObserver::class);
        Expense::observe(GeneralObserver::class);

        // Register Stock Observers
        PurchaseItem::observe(StockItemObserver::class);
        PurchaseReturnItem::observe(StockItemObserver::class);
        SaleItem::observe(StockItemObserver::class);
        SaleReturnItem::observe(StockItemObserver::class);
        AdjustmentItem::observe(StockItemObserver::class);
    }
}
