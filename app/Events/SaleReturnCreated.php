<?php

namespace App\Events;

use App\Models\SaleReturn;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;

class SaleReturnCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SaleReturn $saleReturn)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sales'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->saleReturn->id,
            'total_amount' => $this->saleReturn->total_amount,
            'customer_name' => $this->saleReturn->sale->customer->name ?? 'Guest',
            'created_at' => $this->saleReturn->created_at->toDateTimeString(),
        ];
    }
}
