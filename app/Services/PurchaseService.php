<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\Product;
use App\Events\PurchaseCreated;
use App\Events\PurchasePaymentRecorded;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Store a new purchase.
     */
    public function createPurchase(array $data): Purchase
    {
        $totals = $this->calculateTotals($data['items'], (float) ($data['discount'] ?? 0));

        $purchase = DB::transaction(function () use ($data, $totals) {
            $purchase = Purchase::create([
                'invoice_no' => $data['invoice_no'],
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'purchase_date' => $data['purchase_date'],
                'note' => $data['note'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'payable_amount' => $totals['payable'],
                'paid_amount' => 0,
            ]);

            foreach ($totals['lines'] as $line) {
                PurchaseItem::create(array_merge($line, [
                    'purchase_id' => $purchase->id,
                ]));
            }

            return $purchase;
        });

        event(new PurchaseCreated($purchase));

        return $purchase;
    }

    /**
     * Update an existing purchase.
     */
    public function updatePurchase(Purchase $purchase, array $data): Purchase
    {
        $totals = $this->calculateTotals($data['items'], (float) ($data['discount'] ?? 0));

        return DB::transaction(function () use ($purchase, $data, $totals) {
            $purchase->items()->delete();

            $purchase->update([
                'invoice_no' => $data['invoice_no'],
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'purchase_date' => $data['purchase_date'],
                'note' => $data['note'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'payable_amount' => $totals['payable'],
            ]);

            foreach ($totals['lines'] as $line) {
                PurchaseItem::create(array_merge($line, [
                    'purchase_id' => $purchase->id,
                ]));
            }

            return $purchase;
        });
    }

    /**
     * Store a payment for a purchase.
     */
    public function recordPayment(Purchase $purchase, float $amount, ?int $userId = null): PurchasePayment
    {
        $payment = DB::transaction(function () use ($purchase, $amount, $userId) {
            $payment = PurchasePayment::create([
                'purchase_id' => $purchase->id,
                'user_id' => $userId,
                'amount' => $amount,
            ]);

            $purchase->increment('paid_amount', $amount);

            return $payment;
        });

        event(new PurchasePaymentRecorded($payment));

        return $payment;
    }

    /**
     * Calculate subtotal, discount, and payable for purchase items.
     */
    public function calculateTotals(array $items, float $discount): array
    {
        $subtotal = 0;
        $lines = [];

        foreach ($items as $row) {
            $product = Product::with('unit')->findOrFail($row['product_id']);
            $qty = (float) $row['quantity'];
            $price = (float) $row['unit_price'];
            $lineTotal = round($qty * $price, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $qty,
                'unit_label' => $product->unit->short_name ?? $product->unit->name,
                'unit_price' => $price,
                'line_total' => $lineTotal,
            ];
        }

        $discount = round($discount, 2);
        $payable = round(max(0, $subtotal - $discount), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'payable' => $payable,
            'lines' => $lines,
        ];
    }
}
