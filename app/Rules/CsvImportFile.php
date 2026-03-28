<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class CsvImportFile implements ValidationRule
{
    public function __construct(
        private readonly int $maxKilobytes = 10240
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('A valid file upload is required.');

            return;
        }

        if (! $value->isValid()) {
            $fail('The file upload failed. Try again or use a smaller file.');

            return;
        }

        if ($value->getSize() > $this->maxKilobytes * 1024) {
            $fail("The file may not be greater than {$this->maxKilobytes} kilobytes.");

            return;
        }

        $ext = strtolower($value->getClientOriginalExtension());
        $basename = strtolower($value->getClientOriginalName());

        if (in_array($ext, ['csv', 'txt'], true)) {
            return;
        }

        if (str_ends_with($basename, '.csv') || str_ends_with($basename, '.txt')) {
            return;
        }

        $mime = strtolower((string) ($value->getClientMimeType() ?: $value->getMimeType()));
        $allowedMimes = [
            'text/csv',
            'text/plain',
            'application/csv',
            'text/x-csv',
            'application/vnd.ms-excel',
        ];

        if (in_array($mime, $allowedMimes, true)) {
            return;
        }

        if ($mime === 'application/octet-stream' && (str_contains($basename, '.csv') || str_contains($basename, '.txt'))) {
            return;
        }

        $fail('The file must be a CSV or plain-text (.csv / .txt) file.');
    }
}
