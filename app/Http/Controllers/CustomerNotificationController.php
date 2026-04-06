<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\GeneralSetting;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CustomerNotificationController extends NotificationSettingController
{
    public function __construct(NotificationService $notificationService)
    {
        parent::__construct($notificationService);
    }

    public function singleForm(Customer $customer)
    {
        return view('customers.notifications.single', [
            'customer' => $customer,
        ]);
    }

    public function singleLogPage(Customer $customer)
    {
        $logs = NotificationLog::query()
            ->with(['customer', 'user'])
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        return view('customers.notifications.single.log', [
            'customer' => $customer,
            'logs' => $logs,
        ]);
    }

    public function sendSingle(Request $request, Customer $customer)
    {
        $setting = NotificationSetting::first();
        if (! $setting) {
            return back()->with('error', 'Notification settings not found.');
        }

        $validated = $request->validate([
            'channel' => ['required', 'in:email,sms,both'],
            'subject' => ['nullable', 'string', 'max:255'],
            'email_message' => ['nullable', 'string'],
            'sms_message' => ['nullable', 'string'],
        ]);

        $channel = (string) $validated['channel'];
        $generalSetting = GeneralSetting::first();
        $subject = (string) ($validated['subject'] ?? ('Notification from ' . config('app.name')));
        $emailMessage = (string) ($validated['email_message'] ?? '');
        $smsMessage = (string) ($validated['sms_message'] ?? '');
        $generalSetting = GeneralSetting::first();

        if (($channel === 'email' || $channel === 'both') && trim($emailMessage) === '') {
            return back()->withErrors(['email_message' => 'Message is required for Email.']);
        }
        if (($channel === 'sms' || $channel === 'both') && trim($smsMessage) === '') {
            return back()->withErrors(['sms_message' => 'Message is required for SMS.']);
        }

        $channels = [];

        // Email sending
        if (in_array($channel, ['email', 'both'], true)) {
            if (! $customer->email) {
                return back()->with('error', 'Customer email is missing.');
            }
            $this->setMailConfig($setting);

            $body = $setting->email_body ?? '{{message}}';
            $body = str_replace('{{fullname}}', (string) $customer->name, $body);
            $body = str_replace('{{username}}', (string) ($request->user()?->name ?? $request->user()?->email ?? 'user'), $body);
            $body = str_replace('{{message}}', $emailMessage, $body);
            $body = str_replace('{{site_name}}', (string) config('app.name'), $body);
            $body = str_replace('{{site_currency}}', (string) ($generalSetting?->currency ?? ''), $body);
            $body = str_replace('{{currency_symbol}}', (string) currencySymbol(), $body);

            Mail::html($body, function ($mail) use ($customer, $setting, $subject) {
                $mail->to($customer->email)
                    ->subject($subject)
                    ->from($setting->email_sent_from_email, $setting->email_sent_from_name);
            });
            $channels[] = 'email';
        }

        // SMS sending
        if (in_array($channel, ['sms', 'both'], true)) {
            if (! $customer->phone) {
                return back()->with('error', 'Customer phone/mobile is missing.');
            }

            $sms = $setting->sms_body ?? '{{message}}';
            $sms = str_replace('{{message}}', $smsMessage, $sms);
            $sms = str_replace('{{fullname}}', (string) $customer->name, $sms);
            $sms = str_replace('{{username}}', (string) ($request->user()?->name ?? $request->user()?->email ?? 'user'), $sms);
            $sms = str_replace('{{site_name}}', (string) config('app.name'), $sms);
            $sms = str_replace('{{site_currency}}', (string) ($generalSetting?->currency ?? ''), $sms);
            $sms = str_replace('{{currency_symbol}}', (string) currencySymbol(), $sms);

            $this->sendSms($setting, $customer->phone, $sms);
            $channels[] = 'sms';
        }

        NotificationLog::create([
            'customer_id' => $customer->id,
            'user_id' => $request->user()?->id,
            'channel' => implode(',', $channels),
            'subject' => $subject,
            'message' => ($channel === 'sms') ? $smsMessage : $emailMessage,
        ]);

        return redirect()
            ->route('customers.notifications.single', $customer)
            ->with('success', 'Notification sent successfully.');
    }

    public function allForm()
    {
        return view('customers.notifications.all');
    }

    public function sendAll(Request $request)
    {
        $setting = NotificationSetting::first();
        if (! $setting) {
            return back()->with('error', 'Notification settings not found.');
        }

        $validated = $request->validate([
            'channel' => ['required', 'in:email,sms,both'],
            'subject' => ['nullable', 'string', 'max:255'],
            'email_message' => ['nullable', 'string'],
            'sms_message' => ['nullable', 'string'],
            'start_form' => ['required', 'integer', 'min:1'],
            'per_batch' => ['required', 'integer', 'min:1'],
            'cooling_period' => ['required', 'integer', 'min:0'],
        ]);

        $channel = (string) $validated['channel'];
        // start_form is treated as a starting customer id (matches UI label).
        $startForm = (int) $validated['start_form'];
        $perBatch = (int) $validated['per_batch'];
        $cooling = (int) $validated['cooling_period'];

        if (($channel === 'email' || $channel === 'both') && trim((string) ($validated['email_message'] ?? '')) === '') {
            return back()->withErrors(['email_message' => 'Message is required for Email.']);
        }
        if (($channel === 'sms' || $channel === 'both') && trim((string) ($validated['sms_message'] ?? '')) === '') {
            return back()->withErrors(['sms_message' => 'Message is required for SMS.']);
        }

        $subject = (string) ($validated['subject'] ?? ('Notification from ' . config('app.name')));
        $emailMessage = (string) ($validated['email_message'] ?? '');
        $smsMessage = (string) ($validated['sms_message'] ?? '');

        $customersQuery = Customer::query()
            ->where('id', '>=', $startForm)
            ->orderBy('id');

        $sent = 0;

        $customersQuery->chunk($perBatch, function ($chunk) use ($request, $setting, $validated, $channel, $subject, $emailMessage, $smsMessage, &$sent, $cooling) {
            foreach ($chunk as $customer) {
                $channels = [];
                $didSend = false;

                // Email
                if (in_array($channel, ['email', 'both'], true)) {
                    if ($customer->email) {
                        $this->setMailConfig($setting);
                        $body = $setting->email_body ?? '{{message}}';
                        $body = str_replace('{{fullname}}', (string) $customer->name, $body);
                        $body = str_replace('{{username}}', (string) ($request->user()?->name ?? $request->user()?->email ?? 'user'), $body);
                        $body = str_replace('{{message}}', $emailMessage, $body);
                        $body = str_replace('{{site_name}}', (string) config('app.name'), $body);
                        $body = str_replace('{{site_currency}}', (string) ($generalSetting?->currency ?? ''), $body);
                        $body = str_replace('{{currency_symbol}}', (string) currencySymbol(), $body);

                        Mail::html($body, function ($mail) use ($customer, $setting, $subject) {
                            $mail->to($customer->email)
                                ->subject($subject)
                                ->from($setting->email_sent_from_email, $setting->email_sent_from_name);
                        });
                        $channels[] = 'email';
                        $didSend = true;
                    }
                }

                // SMS
                if (in_array($channel, ['sms', 'both'], true)) {
                    if ($customer->phone) {
                        $sms = $setting->sms_body ?? '{{message}}';
                        $sms = str_replace('{{message}}', $smsMessage, $sms);
                        $sms = str_replace('{{fullname}}', (string) $customer->name, $sms);
                        $sms = str_replace('{{username}}', (string) ($request->user()?->name ?? $request->user()?->email ?? 'user'), $sms);
                        $sms = str_replace('{{site_name}}', (string) config('app.name'), $sms);
                        $sms = str_replace('{{site_currency}}', (string) ($generalSetting?->currency ?? ''), $sms);
                        $sms = str_replace('{{currency_symbol}}', (string) currencySymbol(), $sms);

                        $this->sendSms($setting, $customer->phone, $sms);
                        $channels[] = 'sms';
                        $didSend = true;
                    }
                }

                if (! $didSend) {
                    continue;
                }

                NotificationLog::create([
                    'customer_id' => $customer->id,
                    'user_id' => $request->user()?->id,
                    'channel' => implode(',', $channels),
                    'subject' => $subject,
                    'message' => ($channel === 'sms') ? $smsMessage : $emailMessage,
                ]);

                $sent++;
            }

            if ($cooling > 0) {
                sleep($cooling);
            }
        });

        return back()->with('success', 'Notifications sent. Total: ' . $sent);
    }
}

