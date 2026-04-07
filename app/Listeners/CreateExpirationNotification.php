<?php

namespace App\Listeners;

use App\Events\ProductBatchExpiringAlert;
use App\Models\ProductBatch;
use App\Models\SystemNotification;
use Illuminate\Support\Carbon;

use App\Models\User;

class CreateExpirationNotification
{
    public function handle(ProductBatchExpiringAlert $event): void
    {
        /** @var ProductBatch $batch */
        $batch = $event->batch;
        $daysUntilExpiration = $event->daysUntilExpiration;

        // Determine severity based on days remaining
        $severity = match (true) {
            $daysUntilExpiration < 0 => 'danger',
            $daysUntilExpiration <= 3 => 'danger',
            $daysUntilExpiration <= 7 => 'warning',
            default => 'info',
        };

        // Don't create duplicate notifications for the same batch on the same day for same user
        // Since we are broadcasting to all admins, we'll check later
        
        $message = match (true) {
            $daysUntilExpiration < 0 => "{$batch->product->name} (Batch: {$batch->batch_number}) has already expired!",
            $daysUntilExpiration == 0 => "{$batch->product->name} (Batch: {$batch->batch_number}) expires today!",
            default => "{$batch->product->name} (Batch: {$batch->batch_number}) expires in {$daysUntilExpiration} days",
        };

        // Get all staff members who have permission to manage products
        $staffToNotify = User::whereHas('roles.permissions', function($q) {
            $q->where('name', 'All Product');
        })->get();

        foreach ($staffToNotify as $staff) {
            $existingNotification = SystemNotification::where('user_id', $staff->id)
                ->where('product_batch_id', $batch->id)
                ->where('type', 'expiration_alert')
                ->whereDate('created_at', now())
                ->first();

            if ($existingNotification) continue;

            SystemNotification::create([
                'user_id' => $staff->id,
                'type' => 'expiration_alert',
                'title' => 'Product Expiration Alert',
                'message' => $message,
                'product_id' => $batch->product_id,
                'product_batch_id' => $batch->id,
                'severity' => $severity,
                'meta' => [
                    'warehouse_id' => $batch->warehouse_id,
                    'expiration_date' => $batch->expiration_date->format('Y-m-d'),
                    'quantity_remaining' => $batch->quantity - $batch->quantity_sold,
                ],
            ]);
        }

        // Update alert sent count
        $batch->increment('quantity_alert_sent');
    }
}
