<?php

namespace App\Http\Requests;

use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'return_date' => ['required', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id' => ['required', 'integer', 'exists:purchase_items,id'],
            'items.*.return_quantity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var PurchaseReturn $purchaseReturn */
            $purchaseReturn = $this->route('purchaseReturn');
            $purchase = $purchaseReturn->purchase;
            $excludeId = $purchaseReturn->id;
            $hasPositive = false;
            foreach ($this->input('items', []) as $i => $row) {
                $line = PurchaseItem::find($row['purchase_item_id'] ?? null);
                if (! $line || $line->purchase_id !== $purchase->id) {
                    $validator->errors()->add("items.$i.purchase_item_id", 'Invalid purchase line.');

                    continue;
                }
                $rq = (float) ($row['return_quantity'] ?? 0);
                if ($rq > 0) {
                    $hasPositive = true;
                }
                $remaining = PurchaseReturn::remainingReturnableForPurchaseItem($line, $excludeId);
                if ($rq > $remaining + 0.0001) {
                    $validator->errors()->add("items.$i.return_quantity", 'Return quantity cannot exceed what is still available on this purchase line ('.$remaining.').');
                }
            }
            if (! $hasPositive) {
                $validator->errors()->add('items', 'Enter a return quantity for at least one line.');
            }
        });
    }
}
