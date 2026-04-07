<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $invoice_no
 * @property int $supplier_id
 * @property int $warehouse_id
 * @property \Carbon\Carbon $purchase_date
 * @property string|null $note
 * @property float $subtotal
 * @property float $discount
 * @property float $payable_amount
 * @property float $paid_amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read \App\Models\Supplier $supplier
 * @property-read \App\Models\Warehouse $warehouse
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchaseItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchasePayment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PurchaseReturn[] $purchaseReturns
 * @property-read float $due_amount
 */
class Purchase extends Model
{
    use SoftDeletes;
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
        // Use database-level locking to prevent race conditions
        $max = static::query()
            ->where('invoice_no', 'like', 'P-%')
            ->lockForUpdate()
            ->latest('id')
            ->value(\DB::raw("CAST(SUBSTRING(invoice_no, 3) AS UNSIGNED)")) ?? 0;

        return 'P-'.str_pad((string) ($max + 1), 7, '0', STR_PAD_LEFT);
    }
}
