<?php

use App\Models\GeneralSetting;

if (! function_exists('currencySymbol')) {
    function currencySymbol(): string
    {
        $setting = GeneralSetting::first();

        return $setting?->currency_symbol ?? '$';
    }
}

if (! function_exists('formatCurrency')) {
    function formatCurrency($amount)
    {
        $setting = GeneralSetting::first();
        $formattedAmount = number_format($amount, 2);

        if (! $setting) {
            return '$'.$formattedAmount.' USD';
        }

        switch ($setting->currency_format) {
            case 'symbol':
                return $setting->currency_symbol.$formattedAmount;
            case 'text':
                return $formattedAmount.' '.$setting->currency;
            case 'both':
            default:
                return $setting->currency_symbol.$formattedAmount.' '.$setting->currency;
        }
    }
}
