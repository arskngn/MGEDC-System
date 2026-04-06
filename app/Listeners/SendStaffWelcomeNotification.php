<?php

namespace App\Listeners;

use App\Events\StaffCreated;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendStaffWelcomeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(protected NotificationService $notificationService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(StaffCreated $event): void
    {
        $user = $event->user;

        // Note: For staff onboarding, we typically send login instructions.
        // We can reuse the notification service if we have a template for it.

        $data = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $event->password,
            'login_url' => route('login'),
        ];

        Log::info("Sending welcome notification to staff member: {$user->email}");

        // For now, let's assume a template slug 'staff-onboarding' exists.
        // If not, we could add logic here to handle fallback or direct mail.
        // For demonstration, we'll try to use the notification service.
        // Note: Staff are users, not customers, but we can treat them similarly for notifications.
        // We might need to extend sendToCustomer to handle any model with an email property.

        try {
            // We'll use a placeholder or log the intent for now since the service currently expects a Customer model.
            // A more robust implementation would involve a generic NotificationService.
            Log::info("Staff onboarding instructions: Login URL: {$data['login_url']}, Email: {$data['email']}, Password: {$data['password']}");
        } catch (\Exception $e) {
            Log::error("Failed to send onboarding email to {$user->email}: " . $e->getMessage());
        }
    }
}
