<?php

namespace App\Traits;

use App\Models\Product;

/**
 * Trait CalculatesTotals
 *
 * Provides shared logic for calculating line totals, subtotals, and discounts
 * Used by Sale, Purchase, and Return services to avoid code duplication
 */
trait CalculatesTotals
{
    /**
     * Calculate line items with subtotal and discount applied
     *
     * @param array $items Array of items with product_id, quantity, unit_price
     * @param float $discount Amount to discount from subtotal
     * @param string $itemIdField Field name identifying the item (e.g., 'product_id' or 'purchase_item_id')
     * @param callable|null $itemResolver Optional callback to resolve item data
     * @return array Array with keys: 'lines', 'subtotal', 'discount', 'total'
     */
    protected function calculateLineItems(
        array $items,
        float $discount,
        string $itemIdField = 'product_id',
        ?callable $itemResolver = null
    ): array {
        $subtotal = 0;
        $lines = [];

        foreach ($items as $row) {
            // Use resolver if provided, otherwise fetch product
            if ($itemResolver) {
                $item = $itemResolver($row);
            } else {
                $item = Product::with('unit')->findOrFail($row[$itemIdField]);
            }

            if (!$item) continue;

            $qty = (float) ($row['quantity'] ?? $row['return_quantity'] ?? 0);
            $price = (float) ($row['unit_price'] ?? $item->unit_price ?? 0);
            $lineTotal = round($qty * $price, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                ...$this->buildLineItem($row, $item, $qty, $price, $lineTotal),
            ];
        }

        $discount = round($discount, 2);
        $total = round(max(0, $subtotal - $discount), 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => $discount,
            'lines' => $lines,
            'total' => $total,  // Generic 'total' key; caller can map to 'receivable' or 'payable'
        ];
    }

    /**
     * Build individual line item data
     * Override in service class if custom fields needed
     *
     * @param array $row Raw item row from request
     * @param object $item Resolved item (Product or similar)
     * @param float $qty Quantity
     * @param float $price Unit price
     * @param float $lineTotal Calculated line total
     * @return array Line item data
     */
    protected function buildLineItem(
        array $row,
        object $item,
        float $qty,
        float $price,
        float $lineTotal
    ): array {
        return [
            'product_id' => $item->id,
            'product_name' => $item->name ?? $item->product_name,
            'sku' => $item->sku,
            'quantity' => $qty,
            'unit_label' => $item->unit->short_name ?? $item->unit->name ?? 'unit',
            'unit_price' => $price,
            'line_total' => $lineTotal,
        ];
    }
}
