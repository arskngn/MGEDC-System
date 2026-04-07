<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $sale_return_id
 * @property int $sale_item_id
 * @property int $product_id
 * @property string $product_name
 * @property string|null $sku
 * @property float $sale_quantity
 * @property float $return_quantity
 * @property string|null $unit_label
 * @property float $unit_price
 * @property float $line_total
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\SaleReturn $saleReturn
 * @property-read \App\Models\SaleItem $saleItem
 * @property-read \App\Models\Product $product
 */
class SaleReturnItem extends Model
{
    protected $fillable = [
        'sale_return_id',
        'sale_item_id',
        'product_id',
        'product_name',
        'sku',
        'sale_quantity',
        'return_quantity',
        'unit_label',
        'unit_price',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'sale_quantity' => 'decimal:4',
            'return_quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function saleReturn(): BelongsTo
    {
        return $this->belongsTo(SaleReturn::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

