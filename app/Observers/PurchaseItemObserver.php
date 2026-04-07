<?php

namespace App\Observers;

use App\Models\PurchaseItem;
use App\Models\ProductBatch;

class PurchaseItemObserver
{
    /**
     * Handle the PurchaseItem "created" event.
     */
    public function created(PurchaseItem $purchaseItem): void
    {
        // Only create batch if expiration_date is provided
        if (!$purchaseItem->expiration_date) {
            return;
        }

        $purchase = $purchaseItem->purchase;
        
        // Check if batch already exists
        $existingBatch = ProductBatch::query()
            ->where('product_id', $purchaseItem->product_id)
            ->where('warehouse_id', $purchase->warehouse_id)
            ->where('batch_number', $purchaseItem->batch_number ?? 'BATCH-' . $purchaseItem->id)
            ->first();

        if ($existingBatch) {
            // Update existing batch
            $existingBatch->update([
                'quantity' => $existingBatch->quantity + $purchaseItem->quantity,
            ]);
            return;
        }

        // Create new batch
        ProductBatch::create([
            'product_id' => $purchaseItem->product_id,
            'warehouse_id' => $purchase->warehouse_id,
            'batch_number' => $purchaseItem->batch_number ?? 'BATCH-' . $purchaseItem->id,
            'expiration_date' => $purchaseItem->expiration_date,
            'manufactured_date' => $purchaseItem->manufactured_date,
            'quantity' => $purchaseItem->quantity,
            'quantity_sold' => 0,
            'quantity_alert_sent' => 0,
            'status' => 'active',
        ]);
    }

    /**
     * Handle the PurchaseItem "updated" event.
     */
    public function updated(PurchaseItem $purchaseItem): void
    {
        //
    }

    /**
     * Handle the PurchaseItem "deleted" event.
     */
    public function deleted(PurchaseItem $purchaseItem): void
    {
        // Delete associated batch if no longer needed
        ProductBatch::query()
            ->where('product_id', $purchaseItem->product_id)
            ->where('warehouse_id', $purchaseItem->purchase->warehouse_id)
            ->where('batch_number', $purchaseItem->batch_number ?? 'BATCH-' . $purchaseItem->id)
            ->delete();
    }

    /**
     * Handle the PurchaseItem "restored" event.
     */
    public function restored(PurchaseItem $purchaseItem): void
    {
        //
    }

    /**
     * Handle the PurchaseItem "force deleted" event.
     */
    public function forceDeleted(PurchaseItem $purchaseItem): void
    {
        //
    }
}
