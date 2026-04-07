<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * BusinessEventLogger
 *
 * Structured logging service for important business events.
 * Provides a centralized place to track and monitor business-critical activities
 * like high-value transactions, low stock alerts, payment processing, etc.
 *
 * Events logged here flow to standard Laravel logging channels
 * (file, syslog, etc.) and can be monitored by observability tools.
 */
class BusinessEventLogger
{
    /**
     * Log high-value sale event
     *
     * @param int $saleId
     * @param int $customerId
     * @param float $amount
     * @param int $warehouseId
     * @param int|null $userId
     * @return void
     */
    public static function logHighValueSale(
        int $saleId,
        int $customerId,
        float $amount,
        int $warehouseId,
        ?int $userId = null
    ): void {
        Log::channel('business_events')->info('High-value sale recorded', [
            'event' => 'sale.high_value',
            'sale_id' => $saleId,
            'customer_id' => $customerId,
            'amount' => $amount,
            'warehouse_id' => $warehouseId,
            'user_id' => $userId ?? auth()?->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log low stock alert
     *
     * @param int $productId
     * @param string $sku
     * @param float $currentStock
     * @param float $alertQuantity
     * @return void
     */
    public static function logLowStockAlert(
        int $productId,
        string $sku,
        float $currentStock,
        float $alertQuantity
    ): void {
        Log::channel('business_events')->warning('Low stock alert triggered', [
            'event' => 'inventory.low_stock',
            'product_id' => $productId,
            'sku' => $sku,
            'current_stock' => $currentStock,
            'alert_quantity' => $alertQuantity,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log payment received event
     *
     * @param string $paymentType 'customer_payment' or 'supplier_payment'
     * @param int $entityId
     * @param float $amount
     * @param string $method
     * @param int|null $userId
     * @return void
     */
    public static function logPaymentReceived(
        string $paymentType,
        int $entityId,
        float $amount,
        string $method,
        ?int $userId = null
    ): void {
        Log::channel('business_events')->info('Payment recorded', [
            'event' => 'payment.received',
            'type' => $paymentType,
            'entity_id' => $entityId,
            'amount' => $amount,
            'method' => $method,
            'user_id' => $userId ?? auth()?->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log refund/return processed
     *
     * @param string $entityType 'sale_return' or 'purchase_return'
     * @param int $entityId
     * @param float $amount
     * @param int|null $userId
     * @return void
     */
    public static function logReturnProcessed(
        string $entityType,
        int $entityId,
        float $amount,
        ?int $userId = null
    ): void {
        Log::channel('business_events')->info('Return processed', [
            'event' => 'transaction.return',
            'type' => $entityType,
            'entity_id' => $entityId,
            'amount' => $amount,
            'user_id' => $userId ?? auth()?->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log inventory adjustment
     *
     * @param int $adjustmentId
     * @param int $productId
     * @param float $quantity
     * @param string $reason
     * @param int|null $userId
     * @return void
     */
    public static function logInventoryAdjustment(
        int $adjustmentId,
        int $productId,
        float $quantity,
        string $reason,
        ?int $userId = null
    ): void {
        Log::channel('business_events')->info('Inventory adjusted', [
            'event' => 'inventory.adjusted',
            'adjustment_id' => $adjustmentId,
            'product_id' => $productId,
            'quantity_changed' => $quantity,
            'reason' => $reason,
            'user_id' => $userId ?? auth()?->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log failed payment attempt
     *
     * @param string $paymentType
     * @param int $entityId
     * @param float $amount
     * @param string $reason
     * @param int|null $userId
     * @return void
     */
    public static function logPaymentFailure(
        string $paymentType,
        int $entityId,
        float $amount,
        string $reason,
        ?int $userId = null
    ): void {
        Log::channel('business_events')->error('Payment failed', [
            'event' => 'payment.failed',
            'type' => $paymentType,
            'entity_id' => $entityId,
            'amount' => $amount,
            'reason' => $reason,
            'user_id' => $userId ?? auth()?->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log stock overstock warning
     *
     * @param int $productId
     * @param string $sku
     * @param float $currentStock
     * @return void
     */
    public static function logOverstockWarning(
        int $productId,
        string $sku,
        float $currentStock
    ): void {
        Log::channel('business_events')->warning('Overstock detected', [
            'event' => 'inventory.overstock',
            'product_id' => $productId,
            'sku' => $sku,
            'stock_level' => $currentStock,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Log dangerous concurrent access attempt
     *
     * @param string $operation
     * @param int $recordId
     * @param int|null $userId
     * @return void
     */
    public static function logConcurrencyWarning(
        string $operation,
        int $recordId,
        ?int $userId = null
    ): void {
        Log::channel('business_events')->warning('Concurrent access detected', [
            'event' => 'system.concurrency_warning',
            'operation' => $operation,
            'record_id' => $recordId,
            'user_id' => $userId ?? auth()?->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
