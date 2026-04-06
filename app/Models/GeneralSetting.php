<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/**
 * @property string|null $logo_light
 * @property string|null $logo_dark
 * @property string|null $site_title
 */
class GeneralSetting extends Model
{
    protected $fillable = [
        'site_title',
        'full_company_name',
        'currency',
        'currency_symbol',
        'timezone',
        'records_per_page',
        'currency_format',
        'logo_light',
        'logo_dark',
        'favicon',
        'login_background',
        'email_notification',
        'sms_notification',
    ];

    /**
     * Get logo_light with fallback to default image
     */
    public function getLogoLightAttribute($value)
    {
        if ($value && File::exists(public_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value)))) {
            return $value;
        }

        return 'default-images/logo_light_1774081740.jpg';
    }

    /**
     * Get logo_dark with fallback to default image
     */
    public function getLogoDarkAttribute($value)
    {
        if ($value && File::exists(public_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value)))) {
            return $value;
        }

        return 'default-images/logo_dark_1774081633.jpg';
    }

    /**
     * Get favicon with fallback to default image
     */
    public function getFaviconAttribute($value)
    {
        if ($value && File::exists(public_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value)))) {
            return $value;
        }

        return 'default-images/favicon_1774081633.jpg';
    }

    /**
     * Get login_background with fallback to default image
     */
    public function getLoginBackgroundAttribute($value)
    {
        if ($value && File::exists(public_path(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $value)))) {
            return $value;
        }

        return 'default-images/login_bg_1774081616.jpg';
    }

    /**
     * Check if logo_light is custom (uploaded) vs default
     */
    public function hasCustomLogoLight()
    {
        return ! is_null($this->attributes['logo_light']);
    }

    /**
     * Check if logo_dark is custom (uploaded) vs default
     */
    public function hasCustomLogoDark()
    {
        return ! is_null($this->attributes['logo_dark']);
    }

    /**
     * Check if favicon is custom (uploaded) vs default
     */
    public function hasCustomFavicon()
    {
        return ! is_null($this->attributes['favicon']);
    }

    /**
     * Check if login_background is custom (uploaded) vs default
     */
    public function hasCustomLoginBackground()
    {
        return ! is_null($this->attributes['login_background']);
    }
}
