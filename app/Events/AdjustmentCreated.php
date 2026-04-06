<?php

namespace App\Events;

use App\Models\Adjustment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdjustmentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Adjustment $adjustment)
    {
    }
}
