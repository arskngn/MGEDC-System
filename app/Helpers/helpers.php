<?php

use App\Models\GeneralSetting;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use Illuminate\Support\Facades\Cache;

if (! function_exists('currencySymbol')) {
    function currencySymbol(): string
    {
        return Cache::remember('general_setting_currency_symbol', 86400, function () {
            $setting = GeneralSetting::first();
            return $setting?->currency_symbol ?? '$';
        });
    }
}

if (! function_exists('formatCurrency')) {
    function formatCurrency($amount)
    {
        $symbol = Cache::remember('general_setting_currency_symbol', 86400, function () {
            $setting = GeneralSetting::first();
            return $setting?->currency_symbol ?? '$';
        });
        
        $format = Cache::remember('general_setting_currency_format', 86400, function () {
            $setting = GeneralSetting::first();
            return $setting?->currency_format ?? 'both';
        });
        
        $currency = Cache::remember('general_setting_currency_name', 86400, function () {
            $setting = GeneralSetting::first();
            return $setting?->currency ?? 'USD';
        });

        $formattedAmount = number_format($amount, 2);

        switch ($format) {
            case 'symbol':
                return $symbol.$formattedAmount;
            case 'text':
                return $formattedAmount.' '.$currency;
            case 'both':
            default:
                return $symbol.$formattedAmount.' '.$currency;
        }
    }
}

if (! function_exists('getCachedCategories')) {
    /**
     * Get all categories (with optional caching)
     * Always returns fresh Eloquent models to prevent serialization issues
     *
     * @param bool $keyedByName If true, returns collection keyed by lowercase name
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getCachedCategories($keyedByName = false)
    {
        // Always query fresh to avoid cache serialization issues
        $categories = Category::orderBy('name')->get();

        if ($keyedByName) {
            return $categories->keyBy(fn ($c) => strtolower($c->name));
        }

        return $categories;
    }
}

if (! function_exists('getCachedBrands')) {
    /**
     * Get all brands (with optional caching)
     * Always returns fresh Eloquent models to prevent serialization issues
     *
     * @param bool $keyedByName If true, returns collection keyed by lowercase name
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getCachedBrands($keyedByName = false)
    {
        // Always query fresh to avoid cache serialization issues
        $brands = Brand::orderBy('name')->get();

        if ($keyedByName) {
            return $brands->keyBy(fn ($b) => strtolower($b->name));
        }

        return $brands;
    }
}

if (! function_exists('getCachedUnits')) {
    /**
     * Get all units from cache (24-hour TTL)
     * Prevents repeated database queries for unit lists
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getCachedUnits()
    {
        return Cache::remember('units_all', 86400, function () {
            return Unit::orderBy('name')->get();
        });
    }
}

/**
 * Invalidate category/brand/unit caches
 * Call this when categories, brands, or units are created/updated/deleted
 */
if (! function_exists('invalidateLookupCaches')) {
    function invalidateLookupCaches(): void
    {
        Cache::forget('categories_ordered');
        Cache::forget('brands_ordered');
        Cache::forget('units_all');
    }
}


