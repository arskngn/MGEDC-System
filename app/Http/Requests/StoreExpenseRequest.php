<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_type_id' => ['required', 'exists:expense_types,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'expense_type_id.required' => 'Please select an expense type.',
            'expense_type_id.exists' => 'The selected expense type is invalid.',
            'date.required' => 'The date is required.',
            'date.date' => 'The date must be a valid date.',
            'amount.required' => 'The amount is required.',
            'amount.numeric' => 'The amount must be a number.',
            'amount.min' => 'The amount must be at least 0.01.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'expense_type_id' => $this->integer('expense_type_id'),
            'amount' => str_replace(',', '', $this->string('amount')),
        ]);
    }
}
