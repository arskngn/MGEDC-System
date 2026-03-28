<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_no' => ['required', 'string', 'max:64', 'unique:purchases,invoice_no'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('status', true)],
            'purchase_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'discount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
