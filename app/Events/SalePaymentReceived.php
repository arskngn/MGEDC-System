<?php

namespace App\Events;

use App\Models\SalePayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalePaymentReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SalePayment $payment)
    {
    }
}
