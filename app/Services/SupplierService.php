<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function __construct(protected ImportExportService $importExportService)
    {
    }

    /**
     * Store a new supplier.
     */
    public function createSupplier(array $data): Supplier
    {
        return Supplier::create([
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'email' => $data['email'],
            'company_name' => $data['company_name'] ?? null,
            'address' => $data['address'] ?? null,
        ]);
    }

    /**
     * Update an existing supplier.
     */
    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        $supplier->update([
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'email' => $data['email'],
            'company_name' => $data['company_name'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return $supplier;
    }

    /**
     * Export suppliers to CSV.
     */
    public function exportToCsv(iterable $suppliers): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'name', 'email', 'mobile', 'company_name', 'address', 'payable', 'receivable'
        ];

        $rows = [];
        foreach ($suppliers as $s) {
            $rows[] = [
                $s->name,
                $s->email ?? '',
                $s->phone ?? '',
                $s->company_name ?? '',
                $s->address ?? '',
                (string)($s->payable_total ?? 0),
                (string)($s->receivable_total ?? 0),
            ];
        }

        return $this->importExportService->downloadCsv('suppliers-' . time() . '.csv', $headers, $rows);
    }

    /**
     * Import suppliers from CSV.
     */
    public function importFromCsv(string $path): array
    {
        $opened = $this->importExportService->openCsv($path);
        if (!$opened) {
            return ['error' => 'Could not read the uploaded file or the CSV is empty.'];
        }

        $handle = $opened['handle'];
        $delimiter = $opened['delimiter'];
        $map = $opened['map'];

        $required = ['name', 'email', 'mobile'];
        foreach ($required as $col) {
            if (!isset($map[$col])) {
                fclose($handle);
                return ['error' => 'Missing required column: ' . $col . '. Use the sample template.'];
            }
        }

        $rowNum = 1;
        $imported = 0;
        $skipped = 0;
        $firstError = null;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if ($this->importExportService->isRowEmpty($row)) {
                continue;
            }

            $name = trim((string)($row[$map['name']] ?? ''));
            $email = trim((string)($row[$map['email']] ?? ''));
            $mobile = trim((string)($row[$map['mobile']] ?? ''));
            $companyName = isset($map['company_name']) ? trim((string)($row[$map['company_name']] ?? '')) : null;
            $address = isset($map['address']) ? trim((string)($row[$map['address']] ?? '')) : null;

            if ($name === '' || $email === '' || $mobile === '') {
                $skipped++;
                $firstError ??= 'Row ' . $rowNum . ': name, email, and mobile are required.';
                continue;
            }

            $exists = Supplier::query()
                ->where('email', $email)
                ->orWhere('phone', $mobile)
                ->exists();

            if ($exists) {
                $skipped++;
                $firstError ??= 'Row ' . $rowNum . ': supplier with the same email or mobile already exists.';
                continue;
            }

            try {
                Supplier::create([
                    'name' => $name,
                    'phone' => $mobile,
                    'email' => $email,
                    'company_name' => $companyName !== '' ? $companyName : null,
                    'address' => $address !== '' ? $address : null,
                ]);
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $firstError ??= 'Row ' . $rowNum . ': failed to import (' . $e->getMessage() . ').';
            }
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'firstError' => $firstError,
        ];
    }
}
