<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PurchaseReturnItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;

/**
 * @property int $id
 * @property string $name
 * @property string $sku
 * @property int $category_id
 * @property int $brand_id
 * @property int $unit_id
 * @property float|string $default_purchase_price
 * @property float|string $default_sale_price
 * @property float|string $alert_quantity
 * @property string|null $note
 * @property float $current_stock
 * @property float $total_sold
 * @property Category $category
 * @property Brand $brand
 * @property Unit $unit
 * @property \Illuminate\Database\Eloquent\Collection<PurchaseItem> $purchaseItems
 * @property \Illuminate\Database\Eloquent\Collection<SaleItem> $saleItems
 */
class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'brand_id',
        'unit_id',
        'default_purchase_price',
        'default_sale_price',
        'alert_quantity',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'default_purchase_price' => 'decimal:2',
            'default_sale_price' => 'decimal:2',
            'alert_quantity' => 'decimal:4',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Stock from purchases minus purchase returns (sales module not wired yet).
     */
    public function getCurrentStockAttribute(): float
    {
        $purchased = (float) $this->purchaseItems()->sum('quantity');
        $purchaseReturned = (float) PurchaseReturnItem::where('product_id', $this->id)->sum('return_quantity');
        $sold = (float) SaleItem::where('product_id', $this->id)->sum('quantity');
        $saleReturned = (float) SaleReturnItem::where('product_id', $this->id)->sum('return_quantity');

        return max(0, round($purchased - $purchaseReturned - $sold + $saleReturned, 4));
    }

    /**
     * Placeholder until sales lines exist.
     */
    public function getTotalSoldAttribute(): float
    {
        return (float) SaleItem::where('product_id', $this->id)->sum('quantity');
    }
}
