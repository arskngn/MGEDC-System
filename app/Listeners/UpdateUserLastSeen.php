<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateUserLastSeen
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if ($user instanceof User) {
            // Check if this is the first login
            if ($user->last_seen === null) {
                session()->flash('welcome_first_time', true);
            }

            // Update last seen timestamp
            $user->update([
                'last_seen' => now(),
            ]);

            Log::info("User logged in: {$user->email} from IP: " . request()->ip());
        }
    }
}
