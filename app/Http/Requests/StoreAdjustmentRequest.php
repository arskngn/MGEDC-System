<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tracking_no' => ['required', 'string', 'max:64', 'unique:adjustments,tracking_no'],
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('status', true)],
            'adjustment_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_batch_id' => ['nullable', 'exists:product_batches,id'],
            'items.*.quantity' => ['required', 'numeric'],
            'items.*.type' => ['required', 'in:Added,Removed'],
        ];
    }
}
