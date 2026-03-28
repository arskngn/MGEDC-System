<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'purchase_id',
        'return_invoice_no',
        'return_date',
        'warehouse_id',
        'subtotal',
        'discount',
        'receivable_amount',
        'received_amount',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'receivable_amount' => 'decimal:2',
            'received_amount' => 'decimal:2',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchaseReturnPayment::class);
    }

    public function getDueAmountAttribute(): float
    {
        return max(0, (float) $this->receivable_amount - (float) $this->received_amount);
    }

    public static function nextInvoiceNo(): string
    {
        $max = static::query()
            ->where('return_invoice_no', 'like', 'PR-%')
            ->get()
            ->map(fn ($r) => (int) preg_replace('/\D/', '', substr($r->return_invoice_no, 2)))
            ->max() ?? 0;

        return 'PR-'.str_pad((string) ($max + 1), 7, '0', STR_PAD_LEFT);
    }

    /**
     * Quantity still returnable on this purchase line (excluding another return's lines when editing).
     */
    public static function remainingReturnableForPurchaseItem(PurchaseItem $line, ?int $excludeReturnId = null): float
    {
        $q = PurchaseReturnItem::query()
            ->where('purchase_item_id', $line->id)
            ->whereHas('purchaseReturn', fn ($q) => $q->where('purchase_id', $line->purchase_id));

        if ($excludeReturnId) {
            $q->where('purchase_return_id', '!=', $excludeReturnId);
        }

        $returned = (float) $q->sum('return_quantity');

        return max(0, round((float) $line->quantity - $returned, 4));
    }
}
