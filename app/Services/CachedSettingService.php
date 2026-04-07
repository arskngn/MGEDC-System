<?php

namespace App\Services;

use App\Models\GeneralSetting;
use Illuminate\Support\Facades\Cache;

/**
 * Provides cached access to GeneralSetting to prevent repeated database queries.
 * Cache key invalidates when GeneralSetting is updated.
 */
class CachedSettingService
{
    private const CACHE_KEY = 'general_setting_cached';
    private const CACHE_TTL = 86400; // 24 hours

    /**
     * Get the cached general setting.
     */
    public static function get(): ?GeneralSetting
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return GeneralSetting::first();
        });
    }

    /**
     * Invalidate the cache (call after updates).
     */
    public static function invalidate(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Helper methods for common settings access.
     */
    public static function recordsPerPage(): int
    {
        return self::get()?->records_per_page ?? 15;
    }

    public static function currencySymbol(): string
    {
        return self::get()?->currency_symbol ?? '$';
    }

    public static function currency(): string
    {
        return self::get()?->currency ?? 'USD';
    }

    public static function currencyFormat(): string
    {
        return self::get()?->currency_format ?? 'both';
    }
}
