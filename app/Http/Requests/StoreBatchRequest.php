<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_number' => ['required', 'string', 'max:255'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'expiration_date' => ['required', 'date', 'date_format:Y-m-d'],
            'manufactured_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
        ];
    }

    public function messages(): array
    {
        return [
            'expiration_date.required' => 'Expiration date is required.',
            'expiration_date.date_format' => 'Expiration date must be in YYYY-MM-DD format.',
            'quantity.required' => 'Quantity is required.',
            'quantity.numeric' => 'Quantity must be a valid number.',
            'quantity.min' => 'Quantity must be greater than 0.',
        ];
    }
}
