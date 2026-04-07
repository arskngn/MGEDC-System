<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $customer_id
 * @property int $warehouse_id
 * @property int $user_id
 * @property string $invoice_no
 * @property \Carbon\Carbon $sale_date
 * @property string|null $note
 * @property float $subtotal
 * @property float $discount
 * @property float $receivable_amount
 * @property float $paid_amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read Warehouse $warehouse
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|SaleItem[] $items
 * @property-read float $due_amount
 */
class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'warehouse_id',
        'user_id',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        // Use database-level locking to prevent race conditions
        $max = static::query()
            ->where('invoice_no', 'like', 'S-%')
            ->lockForUpdate()
            ->latest('id')
            ->value(\DB::raw("CAST(SUBSTRING(invoice_no, 3) AS UNSIGNED)")) ?? 0;

        return 'S-'.str_pad((string) ($max + 1), 6, '0', STR_PAD_LEFT);
    }
}

