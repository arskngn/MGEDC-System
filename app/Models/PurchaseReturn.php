<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $purchase_id
 * @property string $return_invoice_no
 * @property \Carbon\Carbon $return_date
 * @property int $warehouse_id
 * @property float $subtotal
 * @property float $discount
 * @property float $receivable_amount
 * @property float $received_amount
 * @property string|null $note
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\Purchase $purchase
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchaseReturnItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchaseReturnPayment[] $payments
 * @property-read float $due_amount
 */
class PurchaseReturn extends Model
{
    use SoftDeletes;
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
        // Use database-level locking to prevent race conditions
        $max = static::query()
            ->where('return_invoice_no', 'like', 'PR-%')
            ->lockForUpdate()
            ->latest('id')
            ->value(\DB::raw("CAST(SUBSTRING(return_invoice_no, 4) AS UNSIGNED)")) ?? 0;

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
