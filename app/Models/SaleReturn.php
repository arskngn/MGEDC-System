<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    protected $fillable = [
        'sale_id',
        'return_invoice_no',
        'return_date',
        'warehouse_id',
        'subtotal',
        'discount',
        'payable_amount',
        'paid_amount',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'payable_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SaleReturnPayment::class);
    }

    public function getDueAmountAttribute(): float
    {
        return max(0, (float) $this->payable_amount - (float) $this->paid_amount);
    }
}

