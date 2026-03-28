<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;

class SystemConfigurationController extends Controller
{
    public function index()
    {
        $setting = GeneralSetting::first();
        return view('settings.system-configuration', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = GeneralSetting::first();
        $setting->email_notification = $request->input('email_notification') == '1';
        $setting->sms_notification = $request->input('sms_notification') == '1';
        $setting->save();

        return back()->with('success', 'System configuration updated successfully.');
    }
}
