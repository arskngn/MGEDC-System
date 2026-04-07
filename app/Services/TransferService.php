<?php

namespace App\Services;

use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Product;
use App\Events\TransferCreated;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\DB;

class TransferService
{
    /**
     * Store a new transfer.
     */
    public function createTransfer(array $data): Transfer
    {
        $transfer = DB::transaction(function () use ($data) {
            // Validate stock at source warehouse
            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();
                
                // If using batches, check batch stock in source warehouse
                if (!empty($item['product_batch_id'])) {
                    $batch = $product->batches()->where('id', $item['product_batch_id'])->first();
                    if (!$batch || ($batch->quantity - $batch->quantity_sold) < (float)$item['quantity']) {
                        throw new InsufficientStockException($product, "Insufficient stock in selected batch for transfer.");
                    }
                } else {
                    // General stock check for warehouse (this would ideally use warehouse-specific stock table, 
                    // but for now we use current_stock as a fallback if batching isn't forced)
                    if ($product->current_stock < (float)$item['quantity']) {
                        throw new InsufficientStockException($product);
                    }
                }
            }

            $transfer = Transfer::create([
                'tracking_no' => $data['tracking_no'],
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'transfer_date' => $data['transfer_date'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) continue;

                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'product_batch_id' => $item['product_batch_id'] ?? null,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_label' => $product->unit?->short_name ?? $product->unit?->name,
                    'quantity' => $item['quantity'],
                    'from_stock' => $product->current_stock,
                ]);
            }

            return $transfer;
        });

        event(new TransferCreated($transfer));

        return $transfer;
    }

    /**
     * Update an existing transfer.
     */
    public function updateTransfer(Transfer $transfer, array $data): Transfer
    {
        return DB::transaction(function () use ($transfer, $data) {
            // Re-validate stock at source warehouse
            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();
                $existingItem = $transfer->items->where('product_id', $product->id)->first();
                $existingQty = $existingItem ? (float)$existingItem->quantity : 0;
                
                // Account for quantity already assigned to this transfer
                $availableStock = (float)$product->current_stock + $existingQty;

                if (!empty($item['product_batch_id'])) {
                    $batch = $product->batches()->where('id', $item['product_batch_id'])->first();
                    $existingBatchQty = ($existingItem && $existingItem->product_batch_id == $item['product_batch_id']) ? $existingQty : 0;
                    $availableBatchStock = ($batch ? ($batch->quantity - $batch->quantity_sold) : 0) + $existingBatchQty;
                    
                    if ($availableBatchStock < (float)$item['quantity']) {
                        throw new InsufficientStockException($product, "Insufficient stock in selected batch for transfer.");
                    }
                } else {
                    if ($availableStock < (float)$item['quantity']) {
                        throw new InsufficientStockException($product);
                    }
                }
            }

            $transfer->update([
                'tracking_no' => $data['tracking_no'],
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'transfer_date' => $data['transfer_date'],
                'note' => $data['note'] ?? null,
            ]);

            $transfer->items()->delete();

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) continue;

                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'product_batch_id' => $item['product_batch_id'] ?? null,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_label' => $product->unit?->short_name ?? $product->unit?->name,
                    'quantity' => $item['quantity'],
                    'from_stock' => $product->current_stock,
                ]);
            }

            return $transfer;
        });
    }

    /**
     * Delete a transfer.
     */
    public function deleteTransfer(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $transfer->items()->delete();
            $transfer->delete();
        });
    }
}
