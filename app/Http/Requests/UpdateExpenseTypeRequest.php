<?php

namespace App\Http\Requests;

use App\Models\ExpenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var ExpenseType $expenseType */
        $expenseType = $this->route('expense_type');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('expense_types', 'name')->ignore($expenseType->id)],
        ];
    }
}
