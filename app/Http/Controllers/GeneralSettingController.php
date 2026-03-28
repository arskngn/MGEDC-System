<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class GeneralSettingController extends Controller
{
    public function index()
    {
        $setting = GeneralSetting::first();
        $currencies = [
            'PHP' => '₱',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CNY' => '¥',
            'INR' => '₹',
            'AUD' => '$',
            'CAD' => '$',
            'SGD' => '$',
            'HKD' => '$',
        ];
        $timezones = \DateTimeZone::listIdentifiers();

        return view('settings.general', compact('setting', 'currencies', 'timezones'));
    }

    public function logoFavicon()
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        $setting = GeneralSetting::first();

        return view('settings.logo-favicon', compact('setting'));
    }

    public function updateLogoFavicon(Request $request)
    {
        if (! auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }
        $request->validate([
            'logo_light' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'logo_dark' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'login_background' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $setting = GeneralSetting::first();
        $path = public_path('system-images');

        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        if ($request->hasFile('logo_light')) {
            // Delete old file if exists
            if ($setting->logo_light && File::exists(public_path($setting->logo_light))) {
                File::delete(public_path($setting->logo_light));
            }
            $filename = 'logo_light_'.time().'.'.$request->logo_light->extension();
            $request->logo_light->move($path, $filename);
            $setting->logo_light = 'system-images/'.$filename;
        }

        if ($request->hasFile('logo_dark')) {
            if ($setting->logo_dark && File::exists(public_path($setting->logo_dark))) {
                File::delete(public_path($setting->logo_dark));
            }
            $filename = 'logo_dark_'.time().'.'.$request->logo_dark->extension();
            $request->logo_dark->move($path, $filename);
            $setting->logo_dark = 'system-images/'.$filename;
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon && File::exists(public_path($setting->favicon))) {
                File::delete(public_path($setting->favicon));
            }
            $filename = 'favicon_'.time().'.'.$request->favicon->extension();
            $request->favicon->move($path, $filename);
            $setting->favicon = 'system-images/'.$filename;
        }

        if ($request->hasFile('login_background')) {
            if ($setting->login_background && File::exists(public_path($setting->login_background))) {
                File::delete(public_path($setting->login_background));
            }
            $filename = 'login_bg_'.time().'.'.$request->login_background->extension();
            $request->login_background->move($path, $filename);
            $setting->login_background = 'system-images/'.$filename;
        }

        $setting->save();

        return back()->with('success', 'Logo & Favicon updated successfully.');
    }

    public function update(Request $request)
    {
        $rules = [
            'timezone' => 'required|string',
            'records_per_page' => 'required|integer|min:1',
            'currency_format' => 'required|in:both,text,symbol',
        ];

        // Only admins can update these fields
        if (auth()->user()->hasRole('admin')) {
            $rules['site_title'] = 'required|string|max:255';
            $rules['full_company_name'] = 'required|string|max:255';
            $rules['currency'] = 'required|string|max:10';
            $rules['currency_symbol'] = 'required|string|max:10';
        }

        $request->validate($rules);

        $setting = GeneralSetting::first();

        $data = $request->all();
        if (! auth()->user()->hasRole('admin')) {
            unset($data['site_title']);
            unset($data['full_company_name']);
            unset($data['currency']);
            unset($data['currency_symbol']);
        }

        $setting->update($data);

        return back()->with('success', 'General settings updated successfully.');
    }
}
