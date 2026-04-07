<?php

namespace App\Observers;

use App\Models\TransferItem;
use App\Models\ProductBatch;

class TransferItemObserver
{
    /**
     * Handle the TransferItem "created" event.
     */
    public function created(TransferItem $transferItem): void
    {
        if (!$transferItem->product_batch_id) {
            return;
        }

        $sourceBatch = ProductBatch::find($transferItem->product_batch_id);
        if (!$sourceBatch) {
            return;
        }

        $transfer = $transferItem->transfer;

        // 1. Decrease source batch (by increasing quantity_sold)
        $sourceBatch->increment('quantity_sold', (float) $transferItem->quantity);
        if ($sourceBatch->quantity_sold >= $sourceBatch->quantity) {
            $sourceBatch->update(['status' => 'sold']);
        }

        // 2. Increase destination batch (create or update)
        $destBatch = ProductBatch::query()
            ->where('product_id', $transferItem->product_id)
            ->where('warehouse_id', $transfer->to_warehouse_id)
            ->where('batch_number', $sourceBatch->batch_number)
            ->first();

        if ($destBatch) {
            $destBatch->increment('quantity', (float) $transferItem->quantity);
            if ($destBatch->status === 'sold' && $destBatch->quantity_sold < $destBatch->quantity) {
                $destBatch->update(['status' => 'active']);
            }
        } else {
            ProductBatch::create([
                'product_id' => $transferItem->product_id,
                'warehouse_id' => $transfer->to_warehouse_id,
                'batch_number' => $sourceBatch->batch_number,
                'expiration_date' => $sourceBatch->expiration_date,
                'manufactured_date' => $sourceBatch->manufactured_date,
                'quantity' => (float) $transferItem->quantity,
                'quantity_sold' => 0,
                'status' => 'active',
            ]);
        }
    }

    /**
     * Handle the TransferItem "deleted" event.
     */
    public function deleted(TransferItem $transferItem): void
    {
        if (!$transferItem->product_batch_id) {
            return;
        }

        $sourceBatch = ProductBatch::find($transferItem->product_batch_id);
        if (!$sourceBatch) {
            return;
        }

        $transfer = $transferItem->transfer;

        // 1. Restore source batch (by decreasing quantity_sold)
        $sourceBatch->decrement('quantity_sold', (float) $transferItem->quantity);
        if ($sourceBatch->status === 'sold' && $sourceBatch->quantity_sold < $sourceBatch->quantity) {
            $sourceBatch->update(['status' => 'active']);
        }

        // 2. Decrease destination batch
        $destBatch = ProductBatch::query()
            ->where('product_id', $transferItem->product_id)
            ->where('warehouse_id', $transfer->to_warehouse_id)
            ->where('batch_number', $sourceBatch->batch_number)
            ->first();

        if ($destBatch) {
            $destBatch->decrement('quantity', (float) $transferItem->quantity);
            // If quantity becomes 0 or less, we might want to delete it or just keep it
        }
    }
}
