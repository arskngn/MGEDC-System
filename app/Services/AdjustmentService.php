<?php

namespace App\Services;

use App\Models\Adjustment;
use App\Models\AdjustmentItem;
use App\Models\Product;
use App\Events\AdjustmentCreated;
use App\Enums\AdjustmentType;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class AdjustmentService
{
    /**
     * Store a new adjustment.
     */
    public function createAdjustment(array $data): Adjustment
    {
        $adjustment = DB::transaction(function () use ($data) {
            $adjustment = Adjustment::create([
                'tracking_no' => $data['tracking_no'],
                'warehouse_id' => $data['warehouse_id'],
                'adjustment_date' => $data['adjustment_date'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                if (!$product) continue;

                // Check stock if removing
                if ($item['type'] === AdjustmentType::Subtraction->value && $product->current_stock < (float)$item['quantity']) {
                    throw new InsufficientStockException($product);
                }

                AdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'product_id' => $product->id,
                    'product_batch_id' => $item['product_batch_id'] ?? null,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'type' => $item['type'],
                    'unit_label' => $product->unit?->short_name ?? $product->unit?->name,
                ]);
            }

            return $adjustment;
        });

        event(new AdjustmentCreated($adjustment));

        return $adjustment;
    }

    /**
     * Update an existing adjustment.
     */
    public function updateAdjustment(Adjustment $adjustment, array $data): Adjustment
    {
        return DB::transaction(function () use ($adjustment, $data) {
            $adjustment->update([
                'tracking_no' => $data['tracking_no'],
                'warehouse_id' => $data['warehouse_id'],
                'adjustment_date' => $data['adjustment_date'],
                'note' => $data['note'] ?? null,
            ]);

            // Re-validate stock before deleting old items
            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                if (!$product) continue;

                $existingItem = $adjustment->items->where('product_id', $product->id)->first();
                $existingEffect = 0;
                if ($existingItem) {
                    $existingEffect = ($existingItem->type === AdjustmentType::Addition->value) 
                        ? (float)$existingItem->quantity 
                        : -(float)$existingItem->quantity;
                }

                $availableStock = (float)$product->current_stock - $existingEffect;
                
                if ($item['type'] === AdjustmentType::Subtraction->value && $availableStock < (float)$item['quantity']) {
                    throw new InsufficientStockException($product);
                }
            }

            // Delete existing items
            $adjustment->items()->delete();

            // Create new items
            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) continue;

                AdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'product_id' => $product->id,
                    'product_batch_id' => $item['product_batch_id'] ?? null,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'type' => $item['type'],
                    'unit_label' => $product->unit?->short_name ?? $product->unit?->name,
                ]);
            }

            return $adjustment;
        });
    }

    /**
     * Delete an adjustment.
     */
    public function deleteAdjustment(Adjustment $adjustment): void
    {
        DB::transaction(function () use ($adjustment) {
            $adjustment->items()->delete();
            $adjustment->delete();
        });
    }
}
