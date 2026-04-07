<?php

namespace App\Listeners;

use App\Events\SaleCreated;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendSaleNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(protected NotificationService $notificationService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(SaleCreated $event): void
    {
        $sale = $event->sale;
        $customer = $sale->customer;

        $data = [
            'invoice_no' => $sale->invoice_no,
            'amount' => number_format((float) $sale->receivable_amount, 2),
            'sale_date' => $sale->sale_date?->format('d M, Y'),
        ];

        // 1. Notify Customer
        if ($customer) {
            $this->notificationService->sendToCustomer($customer, 'sale-invoice', $data);
        } else {
            Log::warning("No customer found for sale #{$sale->invoice_no}. Skipping customer notification.");
        }

        // 2. Notify Relevant Staff (Those who can manage sales)
        $staffToNotify = User::whereHas('roles.permissions', function($q) {
            $q->where('name', 'All Sales');
        })->get();

        foreach ($staffToNotify as $staff) {
            $this->notificationService->sendToAdmin(
                "New Sale: #{$sale->invoice_no}",
                "A new sale has been recorded. Invoice: #{$sale->invoice_no}, Amount: " . number_format($sale->receivable_amount, 2) . ", Customer: " . ($customer->name ?? 'Walk-in'),
                'info',
                $staff->id
            );
        }
    }
}
