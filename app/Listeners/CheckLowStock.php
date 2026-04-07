<?php

namespace App\Listeners;

use App\Events\SaleCreated;
use App\Events\SaleReturnCreated;
use App\Events\PurchaseCreated;
use App\Events\PurchaseReturnCreated;
use App\Events\AdjustmentCreated;
use App\Events\TransferCreated;
use App\Services\NotificationService;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

use App\Models\User;

class CheckLowStock
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(SaleCreated|SaleReturnCreated|PurchaseCreated|PurchaseReturnCreated|AdjustmentCreated|TransferCreated $event): void
    {
        $products = $this->getProductsFromEvent($event);
        
        // Get all users who have permission to manage products
        $staffToNotify = User::whereHas('roles.permissions', function($q) {
            $q->where('name', 'All Product');
        })->get();

        foreach ($products as $product) {
            $currentStock = (float) $product->current_stock;
            $alertQuantity = (float) $product->alert_quantity;

            foreach ($staffToNotify as $staff) {
                if ($currentStock <= 0) {
                    $this->notificationService->sendToAdmin(
                        "Out of Stock: {$product->name}",
                        "Product '{$product->name}' (SKU: {$product->sku}) is out of stock!",
                        'danger',
                        $staff->id
                    );
                } elseif ($alertQuantity > 0 && $currentStock <= $alertQuantity) {
                    $this->notificationService->sendToAdmin(
                        "Low Stock Alert: {$product->name}",
                        "Product '{$product->name}' (SKU: {$product->sku}) has low stock. Current: {$currentStock}, Alert Threshold: {$alertQuantity}",
                        'warning',
                        $staff->id
                    );
                }
            }
            
            Log::info("Stock check for product: {$product->name} (SKU: {$product->sku}). Current stock: {$currentStock}, Alert quantity: {$alertQuantity}");
        }
    }

    /**
     * Extract products from the event.
     *
     * @param SaleCreated|SaleReturnCreated|PurchaseCreated|PurchaseReturnCreated|AdjustmentCreated|TransferCreated $event
     * @return Product[]
     */
    protected function getProductsFromEvent(SaleCreated|SaleReturnCreated|PurchaseCreated|PurchaseReturnCreated|AdjustmentCreated|TransferCreated $event): array
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
