<?php

namespace App\Observers;

use App\Models\AdjustmentItem;
use App\Models\ProductBatch;
use App\Enums\AdjustmentType;

class AdjustmentItemObserver
{
    /**
     * Handle the AdjustmentItem "created" event.
     */
    public function created(AdjustmentItem $adjustmentItem): void
    {
        if (!$adjustmentItem->product_batch_id) {
            return;
        }

        $batch = ProductBatch::find($adjustmentItem->product_batch_id);
        if (!$batch) {
            return;
        }

        if ($adjustmentItem->type === AdjustmentType::Addition->value) {
            // Addition: increase the batch's quantity
            $batch->increment('quantity', (float) $adjustmentItem->quantity);
        } else {
            // Subtraction: increase the batch's quantity_sold
            $batch->increment('quantity_sold', (float) $adjustmentItem->quantity);
            
            // If it was sold, reactivate it if it's no longer sold
            if ($batch->quantity_sold >= $batch->quantity) {
                $batch->update(['status' => 'sold']);
            }
        }
    }

    /**
     * Handle the AdjustmentItem "deleted" event.
     */
    public function deleted(AdjustmentItem $adjustmentItem): void
    {
        if (!$adjustmentItem->product_batch_id) {
            return;
        }

        $batch = ProductBatch::find($adjustmentItem->product_batch_id);
        if (!$batch) {
            return;
        }

        if ($adjustmentItem->type === AdjustmentType::Addition->value) {
            // Restore Addition: decrease the batch's quantity
            $batch->decrement('quantity', (float) $adjustmentItem->quantity);
        } else {
            // Restore Subtraction: decrease the batch's quantity_sold
            $batch->decrement('quantity_sold', (float) $adjustmentItem->quantity);
            
            // If it was sold, reactivate it
            if ($batch->status === 'sold' && $batch->quantity_sold < $batch->quantity) {
                $batch->update(['status' => 'active']);
            }
        }
    }
}
