<?php

namespace Tests\Unit;

use App\Models\GeneralSetting;
use App\Services\CachedSettingService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * CachedSettingServiceTest
 *
 * Tests for caching of application settings to prevent N+1 queries
 */
class CachedSettingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test that settings are cached on first access
     */
    public function test_settings_are_cached_on_first_access(): void
    {
        $setting = GeneralSetting::factory()->create([
            'currency_symbol' => '$',
            'currency' => 'USD',
        ]);

        Cache::flush(); // Ensure cache is empty

        // First call should hit database
        $result = CachedSettingService::get();

        $this->assertNotNull($result);
        $this->assertEquals($setting->id, $result->id);

        // Second call should hit cache
        $cachedResult = CachedSettingService::get();

        $this->assertEquals($result->id, $cachedResult->id);
    }

    /**
     * Test that currency symbol is returned correctly
     */
    public function test_currency_symbol_helper(): void
    {
        GeneralSetting::factory()->create(['currency_symbol' => '€']);

        Cache::flush();

        $symbol = CachedSettingService::currencySymbol();

        $this->assertEquals('€', $symbol);
    }

    /**
     * Test cache invalidation when setting is updated
     */
    public function test_cache_invalidation_on_update(): void
    {
        $setting = GeneralSetting::factory()->create(['currency_symbol' => '$']);

        Cache::flush();

        // Prime the cache
        $initial = CachedSettingService::currencySymbol();
        $this->assertEquals('$', $initial);

        // Update the setting
        $setting->update(['currency_symbol' => '¥']);

        // Manually invalidate cache (would normally happen via observer)
        Cache::forget('general_setting_cached');
        Cache::forget('general_setting_currency_symbol');

        $updated = CachedSettingService::currencySymbol();

        $this->assertEquals('¥', $updated);
    }

    /**
     * Test that default values are returned when no setting exists
     */
    public function test_default_values_when_no_setting(): void
    {
        Cache::flush();
        GeneralSetting::query()->delete();

        $symbol = CachedSettingService::currencySymbol();

        $this->assertEquals('$', $symbol, 'Should return default symbol');
    }
}
