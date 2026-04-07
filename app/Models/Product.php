<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseReturnItem;
use App\Models\ProductBatch;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\AdjustmentItem;
use Illuminate\Support\Facades\DB;

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
 * @property \Carbon\Carbon|null $deleted_at
 * @property Category $category
 * @property Brand $brand
 * @property Unit $unit
 * @property \Illuminate\Database\Eloquent\Collection<PurchaseItem> $purchaseItems
 * @property \Illuminate\Database\Eloquent\Collection<SaleItem> $saleItems
 */
class Product extends Model
{
    use SoftDeletes;
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
        'current_stock',
    ];

    protected function casts(): array
    {
        return [
            'default_purchase_price' => 'decimal:2',
            'default_sale_price' => 'decimal:2',
            'alert_quantity' => 'decimal:4',
            'current_stock' => 'decimal:4',
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

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    /**
     * Calculate stock from purchases minus purchase returns, minus sales, plus sale returns, and adjustments.
     * Uses a single database query with UNION and aggregates for optimal performance.
     */
    public function getCalculatedStock(): float
    {
        $result = DB::selectOne(
            'SELECT 
                COALESCE(SUM(quantity), 0) as purchased,
                COALESCE(SUM(CASE WHEN type = \'purchase_return\' THEN quantity ELSE 0 END), 0) as purchase_returned,
                COALESCE(SUM(CASE WHEN type = \'sale\' THEN quantity ELSE 0 END), 0) as sold,
                COALESCE(SUM(CASE WHEN type = \'sale_return\' THEN quantity ELSE 0 END), 0) as sale_returned,
                COALESCE(SUM(CASE WHEN type = \'adjustment_add\' THEN quantity ELSE 0 END), 0) as adjustment_add,
                COALESCE(SUM(CASE WHEN type = \'adjustment_sub\' THEN quantity ELSE 0 END), 0) as adjustment_sub
            FROM (
                SELECT quantity, \'purchase\' as type FROM purchase_items WHERE product_id = ?
                UNION ALL
                SELECT return_quantity, \'purchase_return\' as type FROM purchase_return_items WHERE product_id = ?
                UNION ALL
                SELECT quantity, \'sale\' as type FROM sale_items WHERE product_id = ?
                UNION ALL
                SELECT return_quantity, \'sale_return\' as type FROM sale_return_items WHERE product_id = ?
                UNION ALL
                SELECT quantity, CONCAT(\'adjustment_\', LOWER(type)) as type FROM adjustment_items WHERE product_id = ?
            ) as stock_movements',
            [$this->id, $this->id, $this->id, $this->id, $this->id]
        );

        $purchased = (float) $result->purchased ?? 0;
        $purchaseReturned = (float) $result->purchase_returned ?? 0;
        $sold = (float) $result->sold ?? 0;
        $saleReturned = (float) $result->sale_returned ?? 0;
        $adjustmentAdd = (float) $result->adjustment_add ?? 0;
        $adjustmentSub = (float) $result->adjustment_sub ?? 0;

        return round($purchased - $purchaseReturned - $sold + $saleReturned + $adjustmentAdd - $adjustmentSub, 4);
    }

    /**
     * Get the total available stock from all active batches in a specific warehouse.
     */
    public function getAvailableBatchStock(int $warehouseId): float
    {
        return (float) $this->batches()
            ->where('warehouse_id', $warehouseId)
            ->whereIn('status', ['active', 'expiring'])
            ->sum(DB::raw('quantity - quantity_sold'));
    }

    /**
     * Recalculate and update the current_stock column.
     */
    public function updateStock(): void
    {
        $this->update([
            'current_stock' => $this->getCalculatedStock()
        ]);
    }

    /**
     * Placeholder until sales lines exist.
     */
    public function getTotalSoldAttribute(): float
    {
        return (float) SaleItem::where('product_id', $this->id)->sum('quantity');
    }
}
