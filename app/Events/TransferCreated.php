<?php

namespace App\Events;

use App\Models\Transfer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransferCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Transfer $transfer)
    {
    }
}
