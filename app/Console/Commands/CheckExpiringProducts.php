<?php

namespace App\Console\Commands;

use App\Events\ProductBatchExpiringAlert;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiringProducts extends Command
{
    protected $signature = 'check:expiring-products {days=7 : Check products expiring within N days}';
    protected $description = 'Check for products that are expiring and create notifications';

    public function handle(): int
    {
        $days = (int) $this->argument('days');
        $today = Carbon::today();
        $checkDate = $today->copy()->addDays($days);

        // Find all product batches that are active and expiring within the specified timeframe
        $expiringBatches = ProductBatch::where('status', 'active')
            ->whereBetween('expiration_date', [$today, $checkDate])
            ->get();

        $this->info("Checking for products expiring within {$days} days...");
        $this->info("Found " . $expiringBatches->count() . " expiring batches.");

        /** @var ProductBatch $batch */
        foreach ($expiringBatches as $batch) {
            $daysUntilExpiration = $batch->daysUntilExpiration();
            
            // Dispatch the alert event
            ProductBatchExpiringAlert::dispatch($batch, $daysUntilExpiration);
            
            $this->line("Notification created for: {$batch->product->name} (Expires in {$daysUntilExpiration} days)");
        }

        // Also check for already expired products
        $expiredBatches = ProductBatch::where('status', 'active')
            ->where('expiration_date', '<', $today)
            ->get();

        /** @var ProductBatch $batch */
        foreach ($expiredBatches as $batch) {
            ProductBatchExpiringAlert::dispatch($batch, -1, 'danger');
            $batch->update(['status' => 'expired']);
            
            $this->line("Alert created for expired product: {$batch->product->name}");
        }

        $this->info("Expiration check completed!");
        return 0;
    }
}
