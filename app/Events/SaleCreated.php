<?php

namespace App\Events;

use App\Models\Sale;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\PrivateChannel;

class SaleCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Sale $sale)
    {
    }

    /**
     * Get the channels the event should broadcast on.
     * Using private channel to prevent unauthorized access to customer data.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('warehouse.'.$this->sale->warehouse_id),
        ];
    }

    /**
     * Get the data to broadcast.
     * Only broadcast sale ID and amount - exclude customer info from public broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->sale->id,
            'receivable_amount' => $this->sale->receivable_amount,
            'created_at' => $this->sale->created_at->toDateTimeString(),
        ];
    }
}
