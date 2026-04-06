<?php

namespace App\Events;

use App\Models\PurchaseReturn;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\Channel;

class PurchaseReturnCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PurchaseReturn $purchaseReturn)
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
            'id' => $this->purchaseReturn->id,
            'receivable_amount' => $this->purchaseReturn->receivable_amount,
            'supplier_name' => $this->purchaseReturn->purchase->supplier->name ?? 'N/A',
            'created_at' => $this->purchaseReturn->created_at->toDateTimeString(),
        ];
    }
}
