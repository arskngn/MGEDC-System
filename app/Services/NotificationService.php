<?php

namespace App\Services;

use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Models\Customer;
use App\Models\User;
use App\Models\GeneralSetting;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Send a notification to a recipient (User or Customer).
     */
    public function send(User|Customer $recipient, string $templateSlug, array $data = [], ?User $sender = null): bool
    {
        $setting = NotificationSetting::first();
        if (!$setting) {
            Log::error('Notification settings not found.');
            return false;
        }

        $template = NotificationTemplate::where('slug', $templateSlug)->first();
        if (!$template) {
            Log::error("Notification template with slug '{$templateSlug}' not found.");
            return false;
        }

        $generalSetting = GeneralSetting::first();
        $channels = [];

        // Email
        if ($template->is_email_enabled && $recipient->email) {
            try {
                $this->setMailConfig($setting);
                $subject = $this->replacePlaceholders($template->subject ?? 'Notification', $recipient, $data, $sender, $generalSetting);
                $body = $this->replacePlaceholders($template->email_body ?? '', $recipient, $data, $sender, $generalSetting);

                Mail::html($body, function ($mail) use ($recipient, $template, $setting, $subject) {
                    $mail->to($recipient->email)
                        ->subject($subject)
                        ->from(
                            $template->email_sent_from_email ?? $setting->email_sent_from_email,
                            $template->email_sent_from_name ?? $setting->email_sent_from_name
                        );
                });
                $channels[] = 'email';
            } catch (\Exception $e) {
                Log::error("Failed to send email to {$recipient->email}: " . $e->getMessage());
            }
        }

        // SMS
        if ($template->is_sms_enabled && property_exists($recipient, 'phone') && $recipient->phone) {
            try {
                $smsBody = $this->replacePlaceholders($template->sms_body ?? '', $recipient, $data, $sender, $generalSetting);
                $this->sendSms($setting, $recipient->phone, $smsBody, $template->sms_sent_from);
                $channels[] = 'sms';
            } catch (\Exception $e) {
                Log::error("Failed to send SMS to {$recipient->phone}: " . $e->getMessage());
            }
        }

        if (!empty($channels)) {
            $logData = [
                'user_id' => $sender?->id,
                'channel' => implode(',', $channels),
                'subject' => $template->subject ?? 'Notification',
                'message' => $template->email_body ?? $template->sms_body ?? '',
            ];

            if ($recipient instanceof Customer) {
                $logData['customer_id'] = $recipient->id;
            } else {
                // If it's a User, we might want to log it differently or just store recipient_id if we have such a field
                // For now, let's keep it simple as the log table might only have customer_id
                $logData['customer_id'] = null; 
            }

            NotificationLog::create($logData);
            return true;
        }

        return false;
    }

    /**
     * Send a notification to a customer (Legacy wrapper).
     */
    public function sendToCustomer(Customer $customer, string $templateSlug, array $data = [], ?User $sender = null): bool
    {
        return $this->send($customer, $templateSlug, $data, $sender);
    }

    /**
     * Send a notification to a user.
     */
    public function sendToUser(User $user, string $templateSlug, array $data = [], ?User $sender = null): bool
    {
        return $this->send($user, $templateSlug, $data, $sender);
    }

    /**
     * Send a notification to an admin or specific users.
     */
    public function sendToAdmin(string $subject, string $message, ?string $severity = 'info', ?int $userId = null): bool
    {
        Log::channel('single')->info("ADMIN NOTIFICATION [{$severity}]: {$subject} - {$message}");

        try {
            // If no specific user ID is provided, this might be a broadcast or system alert.
            // For now, let's assume if userId is null, it's for all admins.
            // But to support individual notifications as requested, we allow passing a userId.
            
            \App\Models\SystemNotification::create([
                'user_id' => $userId ?? Auth::id(), // Fallback to current user if none specified
                'title' => $subject,
                'message' => $message,
                'severity' => $severity,
                'type' => 'system_alert',
                'is_read' => false,
            ]);
            
            // Add to session flash if in a web request
            if (request()->hasSession()) {
                request()->session()->flash($severity === 'danger' ? 'error' : $severity, $message);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to create system notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Replace placeholders in a string.
     */
    protected function replacePlaceholders(string $content, User|Customer $recipient, array $data, ?User $sender, ?GeneralSetting $generalSetting): string
    {
        $placeholders = [
            '{{fullname}}' => $recipient->name,
            '{{username}}' => $sender?->name ?? 'User',
            '{{site_name}}' => config('app.name'),
            '{{site_currency}}' => $generalSetting?->currency ?? '',
            '{{currency_symbol}}' => $generalSetting?->currency_symbol ?? '$',
        ];

        foreach ($data as $key => $value) {
            $placeholders["{{{$key}}}"] = $value;
        }

        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }

    /**
     * Configure mailer dynamically.
     */
    public function setMailConfig(NotificationSetting $setting): void
    {
        $method = $setting->email_method;
        $config = $setting->email_config;

        if ($method === 'smtp') {
            Config::set('mail.mailers.smtp.host', $config['host']);
            Config::set('mail.mailers.smtp.port', $config['port']);
            Config::set('mail.mailers.smtp.encryption', $config['encryption'] ?? null);
            Config::set('mail.mailers.smtp.username', $config['username']);
            Config::set('mail.mailers.smtp.password', $config['password']);
            Config::set('mail.default', 'smtp');
        } elseif ($method === 'sendgrid') {
            Config::set('services.sendgrid.api_key', $config['api_key']);
            Config::set('mail.default', 'sendgrid');
        } elseif ($method === 'mailjet') {
            Config::set('services.mailjet.public_key', $config['public_key']);
            Config::set('services.mailjet.secret_key', $config['secret_key']);
            Config::set('mail.default', 'mailjet');
        }
    }

    /**
     * Send SMS using configured method.
     */
    public function sendSms(NotificationSetting $setting, string $to, string $message, ?string $from = null): void
    {
        $method = $setting->sms_method;
        $config = $setting->sms_config;
        $from = $from ?? $setting->sms_sent_from;

        if ($method === 'nexmo') {
            $basic  = new \Vonage\Client\Credentials\Basic($config['api_key'], $config['api_secret']);
            $client = new \Vonage\Client($basic);

            $response = $client->sms()->send(
                new \Vonage\SMS\Message\SMS($to, $from, $message)
            );

            $message = $response->current();
            if ($message->getStatus() != 0) {
                throw new \Exception("Nexmo Error: SMS sending failed with status code " . $message->getStatus());
            }
        } elseif ($method === 'twilio') {
            $client = new \Twilio\Rest\Client($config['account_sid'], $config['auth_token']);
            $client->messages->create($to, [
                'from' => $config['from_number'],
                'body' => $message
            ]);
        } elseif ($method === 'infobip') {
            $client = new \GuzzleHttp\Client();
            $client->post($config['base_url'] . '/sms/2/text/advanced', [
                'headers' => [
                    'Authorization' => 'App ' . $config['api_key'],
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'messages' => [
                        [
                            'from' => $from,
                            'destinations' => [['to' => $to]],
                            'text' => $message,
                        ]
                    ]
                ]
            ]);
        }
    }
}
