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

class NotificationService
{
    /**
     * Send a notification to a customer.
     */
    public function sendToCustomer(Customer $customer, string $templateSlug, array $data = [], ?User $sender = null): bool
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
        if ($template->is_email_enabled && $customer->email) {
            try {
                $this->setMailConfig($setting);
                $subject = $this->replacePlaceholders($template->subject ?? 'Notification', $customer, $data, $sender, $generalSetting);
                $body = $this->replacePlaceholders($template->email_body ?? '', $customer, $data, $sender, $generalSetting);

                Mail::html($body, function ($mail) use ($customer, $template, $setting, $subject) {
                    $mail->to($customer->email)
                        ->subject($subject)
                        ->from(
                            $template->email_sent_from_email ?? $setting->email_sent_from_email,
                            $template->email_sent_from_name ?? $setting->email_sent_from_name
                        );
                });
                $channels[] = 'email';
            } catch (\Exception $e) {
                Log::error("Failed to send email to {$customer->email}: " . $e->getMessage());
            }
        }

        // SMS
        if ($template->is_sms_enabled && $customer->phone) {
            try {
                $smsBody = $this->replacePlaceholders($template->sms_body ?? '', $customer, $data, $sender, $generalSetting);
                $this->sendSms($setting, $customer->phone, $smsBody, $template->sms_sent_from);
                $channels[] = 'sms';
            } catch (\Exception $e) {
                Log::error("Failed to send SMS to {$customer->phone}: " . $e->getMessage());
            }
        }

        if (!empty($channels)) {
            NotificationLog::create([
                'customer_id' => $customer->id,
                'user_id' => $sender?->id,
                'channel' => implode(',', $channels),
                'subject' => $template->subject ?? 'Notification',
                'message' => $template->email_body ?? $template->sms_body ?? '',
            ]);
            return true;
        }

        return false;
    }

    /**
     * Replace placeholders in a string.
     */
    protected function replacePlaceholders(string $content, Customer $customer, array $data, ?User $sender, ?GeneralSetting $generalSetting): string
    {
        $placeholders = [
            '{{fullname}}' => $customer->name,
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
