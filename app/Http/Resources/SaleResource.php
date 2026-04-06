<?php

namespace App\Http\Resources;

use App\Http\Resources\SaleItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_no' => $this->invoice_no,
            'customer' => $this->customer?->name,
            'warehouse' => $this->warehouse?->name,
            'sale_date' => $this->sale_date?->format('Y-m-d'),
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'receivable_amount' => (float) $this->receivable_amount,
            'paid_amount' => (float) $this->paid_amount,
            'due_amount' => (float) $this->due_amount,
            'note' => $this->note,
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
