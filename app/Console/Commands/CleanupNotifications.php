<?php

namespace App\Console\Commands;

use App\Models\GeneralSetting;
use App\Models\SystemNotification;
use App\Models\NotificationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically delete old notifications based on the retention setting.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = GeneralSetting::first();
        $retentionDays = $settings->notification_retention_days ?? 30;
        
        $cutoffDate = now()->subDays($retentionDays);

        $this->info("Cleaning up notifications older than {$retentionDays} days (before {$cutoffDate})...");

        // Delete system notifications
        $deletedSystemCount = SystemNotification::where('created_at', '<', $cutoffDate)->forceDelete();
        
        // Delete notification logs (customer communications)
        $deletedLogCount = NotificationLog::where('created_at', '<', $cutoffDate)->delete();

        $message = "Notification cleanup completed. Deleted {$deletedSystemCount} system alerts and {$deletedLogCount} communication logs.";
        $this->info($message);
        Log::info($message);

        return Command::SUCCESS;
    }
}
