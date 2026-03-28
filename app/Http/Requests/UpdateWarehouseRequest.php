<?php

namespace App\Http\Requests;

use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Warehouse $warehouse */
        $warehouse = $this->route('warehouse');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('warehouses', 'name')->ignore($warehouse->id)],
            'address' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
