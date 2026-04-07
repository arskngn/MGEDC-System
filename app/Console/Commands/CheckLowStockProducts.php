<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\NotificationService;
use Illuminate\Console\Command;

use App\Models\User;

class CheckLowStockProducts extends Command
{
    protected $signature = 'check:low-stock';
    protected $description = 'Check for products with low or zero stock and create notifications';

    public function __construct(protected NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $products = Product::all();
        $admins = User::whereHas('roles', function($q) {
            $q->where('name', 'Admin')->orWhere('name', 'admin');
        })->get();
        $count = 0;

        foreach ($products as $product) {
            $currentStock = (float) $product->current_stock;
            $alertQuantity = (float) $product->alert_quantity;

            foreach ($admins as $admin) {
                if ($currentStock <= 0) {
                    $this->notificationService->sendToAdmin(
                        "Out of Stock: {$product->name}",
                        "Product '{$product->name}' (SKU: {$product->sku}) is out of stock!",
                        'danger',
                        $admin->id
                    );
                    $count++;
                } elseif ($alertQuantity > 0 && $currentStock <= $alertQuantity) {
                    $this->notificationService->sendToAdmin(
                        "Low Stock Alert: {$product->name}",
                        "Product '{$product->name}' (SKU: {$product->sku}) has low stock. Current: {$currentStock}, Alert Threshold: {$alertQuantity}",
                        'warning',
                        $admin->id
                    );
                    $count++;
                }
            }
        }

        $this->info("Low stock check completed! Total alerts created: {$count}");
        return 0;
    }
}
