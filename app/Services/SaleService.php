<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Product;
use App\Events\SaleCreated;
use App\Exceptions\InsufficientStockException;
use App\Events\SaleReturnCreated;
use App\Events\SalePaymentReceived;
use Illuminate\Support\Facades\DB;

class SaleService
{
    /**
     * Store a new sale.
     */
    public function createSale(array $data): Sale
    {
        $sale = DB::transaction(function () use ($data) {
            // First, lock and validate stock for all items using calculated stock (fresh DB data)
            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();
                
                // Check overall calculated stock
                $calculatedStock = $product->getCalculatedStock();
                if ($calculatedStock < (float)$item['quantity']) {
                    throw new InsufficientStockException($product);
                }

                // Also check available batch stock specifically in THIS warehouse
                $batchStock = $product->getAvailableBatchStock($data['warehouse_id']);
                if ($batchStock < (float)$item['quantity']) {
                    // This is a more specific error: we have stock, but not enough in active batches in this warehouse
                    throw new InsufficientStockException($product, "Insufficient stock in active batches for warehouse.");
                }
            }

            $totals = $this->calculateTotals($data['items'], (float) ($data['discount'] ?? 0));

            $sale = Sale::create([
                'invoice_no' => $data['invoice_no'],
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'sale_date' => $data['sale_date'],
                'note' => $data['note'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'receivable_amount' => $totals['receivable'],
                'paid_amount' => 0,
            ]);

            foreach ($totals['lines'] as $line) {
                SaleItem::create(array_merge($line, [
                    'sale_id' => $sale->id,
                ]));
            }

            return $sale;
        });

        event(new SaleCreated($sale));

        return $sale;
    }

    /**
     * Update an existing sale.
     */
    public function updateSale(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {
            // Re-validate stock: we need to account for the items already in this sale
            // Use calculated stock (fresh DB data) instead of potentially stale current_stock column
            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->firstOrFail();
                $existingItem = $sale->items->where('product_id', $product->id)->first();
                $existingQty = $existingItem ? (float)$existingItem->quantity : 0;
                
                $calculatedStock = $product->getCalculatedStock();
                if (($calculatedStock + $existingQty) < (float)$item['quantity']) {
                    throw new InsufficientStockException($product);
                }

                // Batch stock check
                $batchStock = $product->getAvailableBatchStock($data['warehouse_id']);
                if (($batchStock + $existingQty) < (float)$item['quantity']) {
                    throw new InsufficientStockException($product, "Insufficient batch stock for warehouse.");
                }
            }

            $totals = $this->calculateTotals($data['items'], (float) ($data['discount'] ?? 0));

            $sale->items()->delete();

            $sale->update([
                'invoice_no' => $data['invoice_no'],
                'customer_id' => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'user_id' => $data['user_id'] ?? $sale->user_id,
                'sale_date' => $data['sale_date'],
                'note' => $data['note'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'receivable_amount' => $totals['receivable'],
            ]);

            foreach ($totals['lines'] as $line) {
                SaleItem::create(array_merge($line, [
                    'sale_id' => $sale->id,
                ]));
            }

            return $sale;
        });
    }

    /**
     * Store a payment for a sale.
     */
    public function receivePayment(Sale $sale, float $amount, ?int $userId = null): SalePayment
    {
        $payment = DB::transaction(function () use ($sale, $amount, $userId) {
            $payment = SalePayment::create([
                'sale_id' => $sale->id,
                'user_id' => $userId,
                'amount' => $amount,
            ]);

            $sale->increment('paid_amount', $amount);

            return $payment;
        });

        event(new SalePaymentReceived($payment));

        return $payment;
    }

    /**
     * Store a sale return.
     */
    public function storeReturn(Sale $sale, array $returnItems, float $discount, ?string $note = null, float $restockingFee = 0): SaleReturn
    {
        $saleReturn = DB::transaction(function () use ($sale, $returnItems, $discount, $note, $restockingFee) {
            $subtotal = 0;
            $lines = [];

            foreach ($returnItems as $item) {
                $saleItem = $sale->items->firstWhere('id', $item['sale_item_id']);
                if (!$saleItem) continue;

                $returnQty = (float) $item['return_quantity'];
                $lineTotal = round($returnQty * (float) $saleItem->unit_price, 2);
                $subtotal += $lineTotal;

                $lines[] = [
                    'sale_item_id' => $saleItem->id, // Add this
                    'product_id' => $saleItem->product_id,
                    'product_name' => $saleItem->product_name,
                    'sku' => $saleItem->sku,
                    'sale_quantity' => $saleItem->quantity,
                    'return_quantity' => $returnQty,
                    'unit_label' => $saleItem->unit_label,
                    'unit_price' => $saleItem->unit_price,
                    'line_total' => $lineTotal,
                ];
            }

            $discount = round($discount, 2);
            $restockingFee = round($restockingFee, 2);
            $payable = round(max(0, $subtotal - $discount - $restockingFee), 2);

            $saleReturn = SaleReturn::create([
                'sale_id' => $sale->id,
                'return_invoice_no' => SaleReturn::nextReturnNo(),
                'return_date' => now()->toDateString(),
                'warehouse_id' => $sale->warehouse_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'restocking_fee' => $restockingFee,
                'payable_amount' => $payable,
                'paid_amount' => 0,
                'note' => $note,
            ]);

            foreach ($lines as $line) {
                SaleReturnItem::create(array_merge($line, [
                    'sale_return_id' => $saleReturn->id,
                ]));
            }

            return $saleReturn;
        });

        event(new SaleReturnCreated($saleReturn));

        return $saleReturn;
    }

    /**
     * Calculate subtotal, discount, and receivable for sale items.
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
        $receivable = round(max(0, $subtotal - $discount), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'receivable' => $receivable,
            'lines' => $lines,
        ];
    }
}
