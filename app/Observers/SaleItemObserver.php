<?php

namespace App\Observers;

use App\Models\SaleItem;
use App\Models\ProductBatch;
use App\Models\SaleItemBatch;
use Illuminate\Support\Facades\DB;

class SaleItemObserver
{
    /**
     * Handle the SaleItem "created" event.
     * Update ProductBatch quantity_sold using FEFO (First Expiring, First Out)
     */
    public function created(SaleItem $saleItem): void
    {
        $sale = $saleItem->sale;
        $remainingQty = (float) $saleItem->quantity;

        // Get all available batches for this product in the warehouse, ordered by expiration date (FEFO)
        $batches = ProductBatch::query()
            ->where('product_id', $saleItem->product_id)
            ->where('warehouse_id', $sale->warehouse_id)
            ->whereIn('status', ['active', 'expiring'])
            ->whereRaw('quantity > quantity_sold')
            ->orderBy('expiration_date', 'asc')
            ->orderBy('manufactured_date', 'asc')
            ->lockForUpdate() // Lock batches for consistent updates
            ->get();

        /** @var ProductBatch $batch */
        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            $availableInBatch = (float) ($batch->quantity - $batch->quantity_sold);
            $deductQty = min($availableInBatch, $remainingQty);

            // Record which batch was used for this sale item
            SaleItemBatch::create([
                'sale_item_id' => $saleItem->id,
                'product_batch_id' => $batch->id,
                'quantity' => $deductQty,
            ]);

            // Update the batch quantity_sold
            $batch->increment('quantity_sold', (float) $deductQty);

            // Update batch status if fully sold
            if ($batch->quantity_sold >= $batch->quantity) {
                $batch->update(['status' => 'sold']);
            }

            $remainingQty -= $deductQty;
        }
    }

    /**
     * Handle the SaleItem "deleted" event.
     * Restore ProductBatch quantity_sold from the specific batches used in this sale
     */
    public function deleted(SaleItem $saleItem): void
    {
        // Get all batch allocations for this specific sale item
        $allocations = SaleItemBatch::where('sale_item_id', $saleItem->id)->get();

        /** @var SaleItemBatch $allocation */
        foreach ($allocations as $allocation) {
            $batch = $allocation->productBatch;
            if ($batch) {
                // Restore the quantity_sold
                $batch->decrement('quantity_sold', (float) $allocation->quantity);
                
                // If it was sold, reactivate it
                if ($batch->status === 'sold') {
                    $batch->update(['status' => 'active']);
                }
            }
            
            // Delete the allocation record
            $allocation->delete();
        }
    }

    /**
     * Handle the SaleItem "updated" event.
     * Logic: Restore old batches and re-deduct for new quantity/product
     */
    public function updated(SaleItem $saleItem): void
    {
        // Simplest and safest way: treat as delete then create
        // This handles changes in quantity, product, or warehouse correctly
        $this->deleted($saleItem);
        $this->created($saleItem);
    }
}
