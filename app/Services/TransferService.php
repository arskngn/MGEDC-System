<?php

namespace App\Services;

use App\Models\Transfer;
use App\Models\TransferItem;
use App\Models\Product;
use App\Events\TransferCreated;
use Illuminate\Support\Facades\DB;

class TransferService
{
    /**
     * Store a new transfer.
     */
    public function createTransfer(array $data): Transfer
    {
        $transfer = DB::transaction(function () use ($data) {
            $transfer = Transfer::create([
                'tracking_no' => $data['tracking_no'],
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'transfer_date' => $data['transfer_date'],
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) continue;

                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_label' => $product->unit?->short_name ?? $product->unit?->name,
                    'quantity' => $item['quantity'],
                    'from_stock' => $product->current_stock,
                ]);
            }

            return $transfer;
        });

        event(new TransferCreated($transfer));

        return $transfer;
    }

    /**
     * Update an existing transfer.
     */
    public function updateTransfer(Transfer $transfer, array $data): Transfer
    {
        return DB::transaction(function () use ($transfer, $data) {
            $transfer->update([
                'tracking_no' => $data['tracking_no'],
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'transfer_date' => $data['transfer_date'],
                'note' => $data['note'] ?? null,
            ]);

            $transfer->items()->delete();

            foreach ($data['items'] as $item) {
                $product = Product::find($item['product_id']);
                if (!$product) continue;

                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_label' => $product->unit?->short_name ?? $product->unit?->name,
                    'quantity' => $item['quantity'],
                    'from_stock' => $product->current_stock,
                ]);
            }

            return $transfer;
        });
    }

    /**
     * Delete a transfer.
     */
    public function deleteTransfer(Transfer $transfer): void
    {
        DB::transaction(function () use ($transfer) {
            $transfer->items()->delete();
            $transfer->delete();
        });
    }
}
