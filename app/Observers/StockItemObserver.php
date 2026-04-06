<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockItemObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->updateStock($model);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->updateStock($model);
        
        // If product_id changed, update the old product too
        if ($model->wasChanged('product_id')) {
            $oldProductId = $model->getOriginal('product_id');
            if ($oldProductId) {
                $oldProduct = \App\Models\Product::find($oldProductId);
                if ($oldProduct) {
                    $oldProduct->updateStock();
                }
            }
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->updateStock($model);
    }

    /**
     * Update stock for the product related to the model.
     * Uses database locking for safe concurrent updates.
     */
    protected function updateStock(Model $model): void
    {
        if (isset($model->product_id)) {
            DB::transaction(function () use ($model) {
                $product = \App\Models\Product::where('id', $model->product_id)->lockForUpdate()->first();
                if ($product) {
                    $product->updateStock();
                }
            });
        }
    }
}
