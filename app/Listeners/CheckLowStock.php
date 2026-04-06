<?php

namespace App\Listeners;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class CheckLowStock
{
    /**
     * Handle the event.
     */
    public function handle(mixed $event): void
    {
        $products = $this->getProductsFromEvent($event);

        foreach ($products as $product) {
            if ($product->alert_quantity && $product->current_stock <= $product->alert_quantity) {
                Log::info("Low stock alert for product: {$product->name} (SKU: {$product->sku}). Current stock: {$product->current_stock}, Alert quantity: {$product->alert_quantity}");
                // In a real system, you might send an email or SMS to the admin here.
            }
        }
    }

    /**
     * Extract products from the event.
     */
    protected function getProductsFromEvent(mixed $event): array
    {
        $products = [];

        if (isset($event->sale)) {
            foreach ($event->sale->items as $item) {
                $products[] = $item->product;
            }
        } elseif (isset($event->purchase)) {
            foreach ($event->purchase->items as $item) {
                $products[] = $item->product;
            }
        } elseif (isset($event->saleReturn)) {
            foreach ($event->saleReturn->items as $item) {
                $products[] = $item->product;
            }
        } elseif (isset($event->purchaseReturn)) {
            foreach ($event->purchaseReturn->items as $item) {
                $products[] = $item->product;
            }
        } elseif (isset($event->adjustment)) {
            foreach ($event->adjustment->items as $item) {
                $products[] = $item->product;
            }
        } elseif (isset($event->transfer)) {
            foreach ($event->transfer->items as $item) {
                $products[] = $item->product;
            }
        }

        return array_filter($products);
    }
}
