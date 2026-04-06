<?php

namespace App\Listeners;

use App\Events\ModelChanged;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class LogModelActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ModelChanged $event): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $event->action,
            'model_type' => get_class($event->model),
            'model_id' => $event->model->getKey(),
            'changes' => $event->changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
