<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class NotificationSettingController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    public function index()
    {
        $setting = NotificationSetting::first();
        $templates = NotificationTemplate::all();
        $smtpConfig = [
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'username' => config('mail.mailers.smtp.username'),
            'password' => config('mail.mailers.smtp.password'),
        ];
        return view('settings.notification', compact('setting', 'templates', 'smtpConfig'));
    }

    public function update(Request $request)
    {
        $setting = NotificationSetting::first();
        if (!$setting) {
            $setting = new NotificationSetting();
        }

        if ($request->has('email_method')) {
            $request->validate([
                'email_method' => 'required|string|in:php,smtp,sendgrid,mailjet',
            ]);
            
            $config = [];
            if ($request->email_method === 'smtp') {
                $config = $request->validate([
                    'host' => 'required|string',
                    'port' => 'required|numeric',
                    'encryption' => 'nullable|string',
                    'username' => 'required|string',
                    'password' => 'required|string',
                ]);
            } elseif ($request->email_method === 'sendgrid') {
                $config = $request->validate([
                    'api_key' => 'required|string',
                ]);
            } elseif ($request->email_method === 'mailjet') {
                $config = $request->validate([
                    'public_key' => 'required|string',
                    'secret_key' => 'required|string',
                ]);
            }

            $setting->update([
                'email_method' => $request->email_method,
                'email_config' => $config,
            ]);
        } elseif ($request->has('sms_method')) {
            $request->validate([
                'sms_method' => 'required|string|in:nexmo,twilio,infobip',
            ]);
            
            $config = [];
            if ($request->sms_method === 'nexmo') {
                $config = $request->validate([
                    'api_key' => 'required|string',
                    'api_secret' => 'required|string',
                ]);
            } elseif ($request->sms_method === 'twilio') {
                $config = $request->validate([
                    'account_sid' => 'required|string',
                    'auth_token' => 'required|string',
                    'from_number' => 'required|string',
                ]);
            } elseif ($request->sms_method === 'infobip') {
                $config = $request->validate([
                    'api_key' => 'required|string',
                    'base_url' => 'required|string',
                ]);
            }

            $setting->update([
                'sms_method' => $request->sms_method,
                'sms_config' => $config,
            ]);
        } else {
            // This handles the "Global Template" submission
            $request->validate([
                'email_sent_from_name' => 'nullable|string|max:255',
                'email_sent_from_email' => 'nullable|email|max:255',
                'email_body' => 'nullable|string',
                'sms_sent_from' => 'nullable|string|max:255',
                'sms_body' => 'nullable|string',
            ]);
            
            $setting->update($request->only([
                'email_sent_from_name',
                'email_sent_from_email',
                'email_body',
                'sms_sent_from',
                'sms_body'
            ]));
        }

        return back()->with('success', 'Notification settings updated successfully.');
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        
        $request->validate([
            'subject' => 'nullable|string|max:255',
            'email_body' => 'nullable|string',
            'sms_body' => 'nullable|string',
            'is_email_enabled' => 'nullable|string',
            'is_sms_enabled' => 'nullable|string',
            'email_sent_from_name' => 'nullable|string|max:255',
            'email_sent_from_email' => 'nullable|email|max:255',
            'sms_sent_from' => 'nullable|string|max:255',
            'push_title' => 'nullable|string|max:255',
            'push_body' => 'nullable|string',
            'is_push_enabled' => 'nullable|string',
        ]);

        $template->update([
            'subject' => $request->subject,
            'email_body' => $request->email_body,
            'sms_body' => $request->sms_body,
            'is_email_enabled' => $request->is_email_enabled == '1',
            'is_sms_enabled' => $request->is_sms_enabled == '1',
            'email_sent_from_name' => $request->email_sent_from_name,
            'email_sent_from_email' => $request->email_sent_from_email,
            'sms_sent_from' => $request->sms_sent_from,
            'push_title' => $request->push_title,
            'push_body' => $request->push_body,
            'is_push_enabled' => $request->is_push_enabled == '1',
        ]);

        return back()->with('success', 'Notification template updated successfully.');
    }

    public function sendTestMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $setting = NotificationSetting::first();
        if (!$setting) {
            return back()->with('error', 'Notification settings not found.');
        }

        // Dynamically configure mailer
        $this->setMailConfig($setting);

        try {
            $body = $setting->email_body;
            $body = str_replace('{{fullname}}', 'Test User', $body);
            $body = str_replace('{{username}}', 'testadmin', $body);
            $body = str_replace('{{message}}', 'This is a test email to verify your email configuration settings.', $body);
            $body = str_replace('{{site_name}}', config('app.name'), $body);

            Mail::html($body, function ($message) use ($request, $setting) {
                $message->to($request->email)
                    ->subject('Test Email from ' . config('app.name'))
                    ->from($setting->email_sent_from_email, $setting->email_sent_from_name);
            });

            $successMsg = 'Test email sent successfully to ' . $request->email;
            if (config('mail.default') === 'log') {
                $successMsg .= ' (Logged to storage/logs/laravel.log because sendmail is not found on Windows).';
            }

            return back()->with('success', $successMsg);
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            if (str_contains($errorMsg, '/usr/sbin/sendmail')) {
                $errorMsg = "The default Linux sendmail path was not found. Please use SMTP for Windows or check your sendmail configuration.";
            }
            return back()->with('error', 'Failed to send test email: ' . $errorMsg);
        }
    }

    public function sendTestSms(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
        ]);

        $setting = NotificationSetting::first();
        if (!$setting) {
            return back()->with('error', 'Notification settings not found.');
        }

        $message = $setting->sms_body;
        $message = str_replace('{{message}}', 'This is a test SMS from ' . config('app.name'), $message);

        try {
            $this->sendSms($setting, $request->mobile, $message);
            return back()->with('success', 'Test SMS sent successfully to ' . $request->mobile);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test SMS: ' . $e->getMessage());
        }
    }

    protected function sendSms($setting, $to, $message)
    {
        $this->notificationService->sendSms($setting, $to, $message);
    }

    protected function setMailConfig($setting)
    {
        $this->notificationService->setMailConfig($setting);
    }
}
