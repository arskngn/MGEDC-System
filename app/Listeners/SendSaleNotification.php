<?php

namespace App\Listeners;

use App\Events\SaleCreated;
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

        if (!$customer) {
            Log::warning("No customer found for sale #{$sale->invoice_no}. Skipping notification.");
            return;
        }

        $data = [
            'invoice_no' => $sale->invoice_no,
            'amount' => number_format((float) $sale->receivable_amount, 2),
            'sale_date' => $sale->sale_date?->format('d M, Y'),
        ];

        // Assuming a template with slug 'sale-invoice' exists
        $this->notificationService->sendToCustomer($customer, 'sale-invoice', $data);
    }
}
