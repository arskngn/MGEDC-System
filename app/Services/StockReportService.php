<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturnItem;
use App\Models\SaleItem;
use App\Models\SaleReturnItem;
use App\Models\AdjustmentItem;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * StockReportService
 *
 * Handles generation of stock reports with filtering by warehouse or product.
 * Separates reporting logic from controller to improve testability and reusability.
 */
class StockReportService
{
    /**
     * Get stock report filtered by warehouse
     *
     * @param int $warehouseId
     * @return BaseCollection<array>
     */
    public function getStockByWarehouse(int $warehouseId): BaseCollection
    {
        $warehouses = Warehouse::query()->enabled()->get();
        $warehouse = $warehouses->firstWhere('id', $warehouseId);

        if (!$warehouse) {
            return collect();
        }

        $products = Product::query()->orderBy('name')->get();
        $stockData = collect();

        foreach ($products as $product) {
            $stock = $this->getProductStockForWarehouse($product->id, $warehouseId);

            $stockData->push([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'warehouse_id' => $warehouseId,
                'warehouse_name' => $warehouse->name,
                'stock_quantity' => $stock,
                'alert_quantity' => $product->alert_quantity,
                'unit' => $product->unit?->short_name ?? 'Unit',
                'is_low_stock' => $stock <= $product->alert_quantity,
            ]);
        }

        return $stockData;
    }

    /**
     * Get stock report filtered by product
     *
     * @param int $productId
     * @return BaseCollection<array>
     */
    public function getStockByProduct(int $productId): BaseCollection
    {
        $product = Product::find($productId);
        if (!$product) {
            return collect();
        }

        $warehouses = Warehouse::query()->enabled()->orderBy('name')->get();
        $stockData = collect();

        foreach ($warehouses as $warehouse) {
            $stock = $this->getProductStockForWarehouse($productId, $warehouse->id);

            $stockData->push([
                'product_id' => $productId,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'warehouse_id' => $warehouse->id,
                'warehouse_name' => $warehouse->name,
                'stock_quantity' => $stock,
                'alert_quantity' => $product->alert_quantity,
                'unit' => $product->unit?->short_name ?? 'Unit',
                'is_low_stock' => $stock <= $product->alert_quantity,
            ]);
        }

        return $stockData;
    }

    /**
     * Calculate product stock for a specific warehouse
     *
     * Includes:
     * + Purchases
     * - Purchase returns
     * - Sales
     * + Sale returns
     * + Adjustments (add)
     * - Adjustments (subtract)
     *
     * @param int $productId
     * @param int $warehouseId
     * @return float
     */
    public function getProductStockForWarehouse(int $productId, int $warehouseId): float
    {
        $purchases = PurchaseItem::whereHas('purchase', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        })->where('product_id', $productId)->sum('quantity');

        $purchaseReturns = PurchaseReturnItem::whereHas('purchaseReturn', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        })->where('product_id', $productId)->sum('return_quantity');

        $sales = SaleItem::whereHas('sale', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        })->where('product_id', $productId)->sum('quantity');

        $saleReturns = SaleReturnItem::whereHas('sale', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId);
        })->where('product_id', $productId)->sum('return_quantity');

        $adjustmentsAdd = AdjustmentItem::whereHas('adjustment', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId)->where('type', 'add');
        })->where('product_id', $productId)->sum('quantity');

        $adjustmentsSubtract = AdjustmentItem::whereHas('adjustment', function ($q) use ($warehouseId) {
            $q->where('warehouse_id', $warehouseId)->where('type', 'subtract');
        })->where('product_id', $productId)->sum('quantity');

        return (float) (
            $purchases
            - $purchaseReturns
            - $sales
            + $saleReturns
            + $adjustmentsAdd
            - $adjustmentsSubtract
        );
    }

    /**
     * Get all enabled warehouses
     *
     * @return Collection
     */
    public function getWarehouses(): Collection
    {
        return Warehouse::query()->enabled()->orderBy('name')->get();
    }

    /**
     * Get all products
     *
     * @return Collection
     */
    public function getProducts(): Collection
    {
        return Product::query()->orderBy('name')->get(['id', 'name', 'sku']);
    }
}
