<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_no' => ['required', 'string', 'max:64', 'unique:sales,invoice_no'],
            'customer_id' => ['required', 'exists:customers,id'],
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('status', true)],
            'sale_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
            'discount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
