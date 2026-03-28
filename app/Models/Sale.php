<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'customer_id',
        'warehouse_id',
        'invoice_no',
        'sale_date',
        'note',
        'subtotal',
        'discount',
        'receivable_amount',
        'paid_amount',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'receivable_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function saleReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function getDueAmountAttribute(): float
    {
        return max(0, (float) $this->receivable_amount - (float) $this->paid_amount);
    }

    public function hasReturns(): bool
    {
        return $this->saleReturns()->exists();
    }

    public static function nextInvoiceNo(): string
    {
        $max = static::query()
            ->where('invoice_no', 'like', 'S-%')
            ->get()
            ->map(fn ($s) => (int) preg_replace('/\D/', '', substr($s->invoice_no, 2)))
            ->max() ?? 0;

        return 'S-'.str_pad((string) ($max + 1), 6, '0', STR_PAD_LEFT);
    }
}

