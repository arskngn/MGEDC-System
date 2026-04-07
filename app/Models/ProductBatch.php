<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $product_id
 * @property int $warehouse_id
 * @property string $batch_number
 * @property Carbon $expiration_date
 * @property Carbon|null $manufactured_date
 * @property float $quantity
 * @property float $quantity_sold
 * @property int $quantity_alert_sent
 * @property string $status
 * @property-read Product $product
 * @property-read Warehouse $warehouse
 * @property-read \Illuminate\Database\Eloquent\Collection<SaleItemBatch> $saleItemBatches
 */
class ProductBatch extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'batch_number',
        'expiration_date',
        'manufactured_date',
        'quantity',
        'quantity_sold',
        'quantity_alert_sent',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
            'manufactured_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function saleItemBatches(): HasMany
    {
        return $this->hasMany(SaleItemBatch::class);
    }

    public function isExpired(): bool
    {
        return $this->expiration_date < Carbon::today();
    }

    public function isExpiringWithin($days = 7): bool
    {
        $expiringDate = Carbon::today()->addDays($days);
        return $this->expiration_date >= Carbon::today() && $this->expiration_date <= $expiringDate;
    }

    public function daysUntilExpiration(): int
    {
        return Carbon::today()->diffInDays($this->expiration_date);
    }

    public function percentageUsed(): float
    {
        if ($this->quantity == 0) {
            return 0;
        }
        return ($this->quantity_sold / $this->quantity) * 100;
    }
}
