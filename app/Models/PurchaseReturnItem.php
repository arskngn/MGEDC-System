<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $purchase_return_id
 * @property int $purchase_item_id
 * @property int $product_id
 * @property string $product_name
 * @property string|null $sku
 * @property float $purchase_quantity
 * @property float $stock_quantity
 * @property float $return_quantity
 * @property string|null $unit_label
 * @property float $unit_price
 * @property float $line_total
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Models\PurchaseReturn $purchaseReturn
 * @property-read \App\Models\PurchaseItem $purchaseItem
 * @property-read \App\Models\Product $product
 */
class PurchaseReturnItem extends Model
{
    protected $fillable = [
        'purchase_return_id',
        'purchase_item_id',
        'product_id',
        'product_name',
        'sku',
        'purchase_quantity',
        'stock_quantity',
        'return_quantity',
        'unit_label',
        'unit_price',
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'purchase_quantity' => 'decimal:4',
            'stock_quantity' => 'decimal:4',
            'return_quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
