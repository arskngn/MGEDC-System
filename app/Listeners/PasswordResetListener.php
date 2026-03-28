<?php

namespace App\Listeners;

use App\Models\GeneralSetting;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class PasswordResetListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        $setting = GeneralSetting::first();

        if (!$setting || !$setting->email_notification) {
            // Prevent the notification from being sent
            Notification::fake();
        }
    }
}
