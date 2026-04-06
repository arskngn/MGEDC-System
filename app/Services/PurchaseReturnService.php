<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\PurchaseReturnPayment;
use App\Events\PurchaseReturnCreated;
use Illuminate\Support\Facades\DB;

class PurchaseReturnService
{
    /**
     * Store a new purchase return.
     */
    public function createReturn(Purchase $purchase, array $data): PurchaseReturn
    {
        $totals = $this->calculateTotals($purchase, $data['items'], (float) ($data['discount'] ?? 0));

        $ret = DB::transaction(function () use ($purchase, $data, $totals) {
            $ret = PurchaseReturn::create([
                'purchase_id' => $purchase->id,
                'return_invoice_no' => PurchaseReturn::nextInvoiceNo(),
                'return_date' => $data['return_date'],
                'warehouse_id' => $purchase->warehouse_id,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'receivable_amount' => $totals['receivable'],
                'received_amount' => 0,
                'note' => $data['note'] ?? null,
            ]);

            foreach ($totals['lines'] as $line) {
                PurchaseReturnItem::create(array_merge($line, [
                    'purchase_return_id' => $ret->id,
                ]));
            }

            return $ret;
        });

        event(new PurchaseReturnCreated($ret));

        return $ret;
    }

    /**
     * Update an existing purchase return.
     */
    public function updateReturn(PurchaseReturn $purchaseReturn, array $data): PurchaseReturn
    {
        $purchase = $purchaseReturn->purchase;
        $totals = $this->calculateTotals($purchase, $data['items'], (float) ($data['discount'] ?? 0), $purchaseReturn->id);

        return DB::transaction(function () use ($purchaseReturn, $data, $totals) {
            $purchaseReturn->items()->delete();

            $purchaseReturn->update([
                'return_date' => $data['return_date'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'receivable_amount' => $totals['receivable'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($totals['lines'] as $line) {
                PurchaseReturnItem::create(array_merge($line, [
                    'purchase_return_id' => $purchaseReturn->id,
                ]));
            }

            return $purchaseReturn;
        });
    }

    /**
     * Record a payment for a purchase return.
     */
    public function recordPayment(PurchaseReturn $purchaseReturn, float $amount, ?int $userId = null): PurchaseReturnPayment
    {
        return DB::transaction(function () use ($purchaseReturn, $amount, $userId) {
            $payment = PurchaseReturnPayment::create([
                'purchase_return_id' => $purchaseReturn->id,
                'user_id' => $userId,
                'amount' => $amount,
                'paid_at' => now(),
            ]);

            $purchaseReturn->increment('received_amount', $amount);

            return $payment;
        });
    }

    /**
     * Calculate subtotal, discount, and receivable for purchase return items.
     */
    public function calculateTotals(Purchase $purchase, array $items, float $discount, ?int $excludeReturnId = null): array
    {
        $subtotal = 0;
        $lines = [];

        foreach ($items as $row) {
            $purchaseItem = $purchase->items->firstWhere('id', $row['purchase_item_id']);
            if (!$purchaseItem) continue;

            $qty = (float) $row['return_quantity'];
            $price = (float) $purchaseItem->unit_price;
            $lineTotal = round($qty * $price, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'purchase_item_id' => $purchaseItem->id,
                'product_id' => $purchaseItem->product_id,
                'product_name' => $purchaseItem->product_name,
                'sku' => $purchaseItem->sku,
                'purchase_quantity' => (float) $purchaseItem->quantity,
                'stock_quantity' => PurchaseReturn::remainingReturnableForPurchaseItem($purchaseItem, $excludeReturnId),
                'return_quantity' => $qty,
                'unit_label' => $purchaseItem->unit_label,
                'unit_price' => $price,
                'line_total' => $lineTotal,
            ];
        }

        $discount = round($discount, 2);
        $receivable = round(max(0, $subtotal - $discount), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'receivable' => $receivable,
            'lines' => $lines,
        ];
    }
}
