<?php

namespace App\Listeners;

use App\Events\ModelChanged;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogModelActivity
{
    /**
     * Handle the event.
     * 
     * Properly handles user audit in various contexts:
     * - Web requests: Uses Auth::id()
     * - Queue jobs: Uses user_id from event (must be set by caller)
     * - CLI/API: Uses user_id from event or defaults to null
     */
    public function handle(ModelChanged $event): void
    {
        // Get user_id: prefer event property, fallback to Auth::id() in web context
        $userId = $event->userId ?? Auth::id();

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $event->action,
            'model_type' => get_class($event->model),
            'model_id' => $event->model->getKey(),
            'changes' => $event->changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
