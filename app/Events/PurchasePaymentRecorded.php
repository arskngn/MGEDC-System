<?php

namespace App\Events;

use App\Models\PurchasePayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchasePaymentRecorded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PurchasePayment $payment)
    {
    }
}
