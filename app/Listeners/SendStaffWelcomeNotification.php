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

        $data = [
            'email' => $user->email,
            'password' => $event->password,
            'login_url' => route('login'),
        ];

        Log::info("Sending welcome notification to staff member: {$user->email}");

        try {
            $this->notificationService->sendToUser($user, 'staff-onboarding', $data);
            
            // Create a system notification for the NEW USER themselves
            \App\Models\SystemNotification::create([
                'user_id' => $user->id,
                'title' => 'Welcome to ' . config('app.name') . '!',
                'message' => "Hello {$user->name}, welcome to the team! We're excited to have you on board. Please explore the dashboard to get started.",
                'severity' => 'info',
                'type' => 'welcome',
                'is_read' => false,
            ]);

            // Also create a system notification for the admin
            $this->notificationService->sendToAdmin(
                "New Staff Member: {$user->name}",
                "A new staff member '{$user->name}' ({$user->email}) has been added to the system.",
                'info'
            );
        } catch (\Exception $e) {
            Log::error("Failed to send onboarding email to {$user->email}: " . $e->getMessage());
        }
    }
}
