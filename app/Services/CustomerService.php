<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(protected ImportExportService $importExportService)
    {
    }

    /**
     * Store a new customer.
     */
    public function createCustomer(array $data): Customer
    {
        return Customer::create([
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'email' => $data['email'],
            'address' => $data['address'] ?? null,
        ]);
    }

    /**
     * Update an existing customer.
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $customer->update([
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'email' => $data['email'],
            'address' => $data['address'] ?? null,
        ]);

        return $customer;
    }

    /**
     * Export customers to CSV.
     */
    public function exportToCsv(iterable $customers): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'name', 'email', 'mobile', 'address', 'receivable', 'payable'
        ];

        $rows = [];
        foreach ($customers as $c) {
            $rows[] = [
                $c->name,
                $c->email ?? '',
                $c->phone ?? '',
                $c->address ?? '',
                (string)($c->receivable_total ?? 0),
                (string)($c->payable_total ?? 0),
            ];
        }

        return $this->importExportService->downloadCsv('customers-' . time() . '.csv', $headers, $rows);
    }

    /**
     * Import customers from CSV.
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
            $address = isset($map['address']) ? trim((string)($row[$map['address']] ?? '')) : null;

            if ($name === '' || $email === '' || $mobile === '') {
                $skipped++;
                $firstError ??= 'Row ' . $rowNum . ': name, email, and mobile are required.';
                continue;
            }

            $exists = Customer::query()
                ->where('email', $email)
                ->orWhere('phone', $mobile)
                ->exists();

            if ($exists) {
                $skipped++;
                $firstError ??= 'Row ' . $rowNum . ': customer with the same email or mobile already exists.';
                continue;
            }

            try {
                Customer::create([
                    'name' => $name,
                    'phone' => $mobile,
                    'email' => $email,
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
