<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $sale_id
 * @property string $return_invoice_no
 * @property \Carbon\Carbon $return_date
 * @property int $warehouse_id
 * @property float $subtotal
 * @property float $discount
 * @property float $payable_amount
 * @property float $paid_amount
 * @property string|null $note
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\Sale $sale
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SaleReturnItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SaleReturnPayment[] $payments
 * @property-read float $due_amount
 */
class SaleReturn extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'sale_id',
        'return_invoice_no',
        'return_date',
        'warehouse_id',
        'subtotal',
        'discount',
        'restocking_fee',
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
            'restocking_fee' => 'decimal:2',
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

    public static function nextReturnNo(): string
    {
        $max = static::query()
            ->where('return_invoice_no', 'like', 'SR-%')
            ->get()
            ->map(fn ($r) => (int) preg_replace('/\D/', '', substr($r->return_invoice_no, 3)))
            ->max() ?? 0;

        return 'SR-'.str_pad((string) ($max + 1), 6, '0', STR_PAD_LEFT);
    }
}

