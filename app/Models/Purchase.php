<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'invoice_no',
        'supplier_id',
        'warehouse_id',
        'purchase_date',
        'note',
        'subtotal',
        'discount',
        'payable_amount',
        'paid_amount',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'payable_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function getDueAmountAttribute(): float
    {
        return max(0, (float) $this->payable_amount - (float) $this->paid_amount);
    }

    public function hasReturns(): bool
    {
        return $this->purchaseReturns()->exists();
    }

    public static function nextInvoiceNo(): string
    {
        $max = static::query()
            ->where('invoice_no', 'like', 'P-%')
            ->get()
            ->map(fn ($p) => (int) preg_replace('/\D/', '', substr($p->invoice_no, 2)))
            ->max() ?? 0;

        return 'P-'.str_pad((string) ($max + 1), 7, '0', STR_PAD_LEFT);
    }
}
