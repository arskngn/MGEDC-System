<?php

namespace Database\Seeders;

use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class AdjustmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = Warehouse::all();
        $products = Product::all();

        if ($warehouses->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Create multiple adjustments
        $adjustments = [
            [
                'tracking_no' => 'ADJ000001',
                'warehouse_id' => $warehouses->first()->id,
                'adjustment_date' => now()->subDays(15)->toDateString(),
                'note' => 'Stock reconciliation after inventory count',
                'items' => [
                    ['product_id' => $products->random()->id, 'adjust_qty' => 50, 'type' => 'Add'],
                    ['product_id' => $products->random()->id, 'adjust_qty' => 25, 'type' => 'Remove'],
                ]
            ],
            [
                'tracking_no' => 'ADJ000002',
                'warehouse_id' => $warehouses->random()->id,
                'adjustment_date' => now()->subDays(10)->toDateString(),
                'note' => 'Damage stock adjustment',
                'items' => [
                    ['product_id' => $products->random()->id, 'adjust_qty' => 10, 'type' => 'Remove'],
                ]
            ],
            [
                'tracking_no' => 'ADJ000003',
                'warehouse_id' => $warehouses->random()->id,
                'adjustment_date' => now()->subDays(5)->toDateString(),
                'note' => 'Opening stock entry',
                'items' => [
                    ['product_id' => $products->random()->id, 'adjust_qty' => 100, 'type' => 'Add'],
                    ['product_id' => $products->random()->id, 'adjust_qty' => 75, 'type' => 'Add'],
                    ['product_id' => $products->random()->id, 'adjust_qty' => 50, 'type' => 'Add'],
                ]
            ],
            [
                'tracking_no' => 'ADJ000004',
                'warehouse_id' => $warehouses->random()->id,
                'adjustment_date' => now()->subDays(2)->toDateString(),
                'note' => 'Return from damaged goods',
                'items' => [
                    ['product_id' => $products->random()->id, 'adjust_qty' => 5, 'type' => 'Add'],
                ]
            ],
            [
                'tracking_no' => 'ADJ000005',
                'warehouse_id' => $warehouses->random()->id,
                'adjustment_date' => now()->toDateString(),
                'note' => 'Monthly physical count adjustment',
                'items' => [
                    ['product_id' => $products->random()->id, 'adjust_qty' => 30, 'type' => 'Add'],
                    ['product_id' => $products->random()->id, 'adjust_qty' => 15, 'type' => 'Remove'],
                    ['product_id' => $products->random()->id, 'adjust_qty' => 20, 'type' => 'Add'],
                ]
            ],
        ];

        foreach ($adjustments as $adjustmentData) {
            $adjustment = Adjustment::create([
                'tracking_no' => $adjustmentData['tracking_no'],
                'warehouse_id' => $adjustmentData['warehouse_id'],
                'adjustment_date' => $adjustmentData['adjustment_date'],
                'note' => $adjustmentData['note'],
            ]);

            foreach ($adjustmentData['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $currentStock = $product->current_stock;

                AdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $currentStock,
                    'adjust_qty' => $itemData['adjust_qty'],
                    'type' => $itemData['type'],
                    'unit_label' => $product->unit->short_name ?? $product->unit->name,
                ]);
            }
        }
    }
}
