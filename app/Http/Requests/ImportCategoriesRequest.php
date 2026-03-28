<?php

namespace App\Http\Requests;

use App\Rules\CsvImportFile;
use Illuminate\Foundation\Http\FormRequest;

class ImportCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'csv_file' => ['required', new CsvImportFile(5120)],
        ];
    }
}
