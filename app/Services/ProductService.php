<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Traits\CsvSanitization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ProductService
{
    use CsvSanitization;

    public function __construct(protected ImportExportService $importExportService)
    {
    }

    /**
     * Store a new product.
     */
    public function createProduct(array $data): Product
    {
        return Product::create([
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'],
            'sku' => $data['sku'],
            'unit_id' => $data['unit_id'],
            'alert_quantity' => $data['alert_quantity'],
            'note' => $data['note'] ?? null,
            'default_purchase_price' => 0,
        ]);
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $product->update([
            'name' => $data['name'],
            'category_id' => $data['category_id'],
            'brand_id' => $data['brand_id'],
            'sku' => $data['sku'],
            'unit_id' => $data['unit_id'],
            'alert_quantity' => $data['alert_quantity'],
            'note' => $data['note'] ?? null,
        ]);

        return $product;
    }

    /**
     * Export products to CSV.
     */
    public function exportToCsv(iterable $products): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $headers = [
            'SKU', 'Name', 'Category', 'Brand', 'Stock', 'Unit', 'Total Sale', 'Alert Qty', 'Note'
        ];

        $rows = [];
        foreach ($products as $p) {
            $rows[] = [
                $p->sku,
                $p->name,
                $p->category?->name ?? '',
                $p->brand?->name ?? '',
                rtrim(rtrim(number_format((float) $p->current_stock, 4, '.', ''), '0'), '.') ?: '0',
                $p->unit->short_name ?? $p->unit->name,
                (string) $p->total_sold,
                rtrim(rtrim(number_format((float) $p->alert_quantity, 4, '.', ''), '0'), '.') ?: '0',
                $p->note ?? '',
            ];
        }

        // Sanitize rows to prevent formula injection in spreadsheet applications
        $sanitizedRows = $this->sanitizeCsvRows($rows);

        return $this->importExportService->downloadCsv('products-' . time() . '.csv', $headers, $sanitizedRows);
    }

    /**
     * Import products from CSV.
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

        $required = ['name', 'category', 'sku', 'brand', 'unit', 'alert_quantity'];
        foreach ($required as $col) {
            if (!isset($map[$col])) {
                fclose($handle);
                return ['error' => 'Missing required column: ' . $col . '. Use the sample template.'];
            }
        }

        $categories = getCachedCategories(keyedByName: true);
        $brands = getCachedBrands(keyedByName: true);
        $units = getCachedUnits();
        $unitByShort = $units->keyBy(fn($u) => strtolower($u->short_name));
        $unitByName = $units->keyBy(fn($u) => strtolower($u->name));

        $rowNum = 1;
        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if ($this->importExportService->isRowEmpty($row)) {
                continue;
            }

            $name = trim((string)($row[$map['name']] ?? ''));
            $sku = trim((string)($row[$map['sku']] ?? ''));
            if ($name === '' && $sku === '') {
                continue;
            }

            $categoryName = strtolower(trim((string)($row[$map['category']] ?? '')));
            $brandName = strtolower(trim((string)($row[$map['brand']] ?? '')));
            $unitKey = strtolower(trim((string)($row[$map['unit']] ?? '')));
            $alertRaw = trim((string)($row[$map['alert_quantity']] ?? ''));
            $note = isset($map['note']) ? trim((string)($row[$map['note']] ?? '')) : '';

            if ($name === '' || $sku === '' || $categoryName === '' || $brandName === '' || $unitKey === '' || $alertRaw === '') {
                $errors[] = "Row {$rowNum}: All of name, category, sku, brand, unit, and alert_quantity are required.";
                continue;
            }

            if (!is_numeric($alertRaw) || (float)$alertRaw < 0) {
                $errors[] = "Row {$rowNum}: alert_quantity must be a non-negative number.";
                continue;
            }

            $category = $categories->get($categoryName);
            if (!$category) {
                $errors[] = "Row {$rowNum}: Unknown category \"{$row[$map['category']]}\".";
                continue;
            }

            $brand = $brands->get($brandName);
            if (!$brand) {
                $errors[] = "Row {$rowNum}: Unknown brand \"{$row[$map['brand']]}\".";
                continue;
            }

            $unit = $unitByShort->get($unitKey) ?? $unitByName->get($unitKey);
            if (!$unit) {
                $errors[] = "Row {$rowNum}: Unknown unit \"{$row[$map['unit']]}\".";
                continue;
            }

            if (Product::where('sku', $sku)->exists()) {
                $errors[] = "Row {$rowNum}: SKU \"{$sku}\" already exists.";
                continue;
            }

            if (Product::where('name', $name)->exists()) {
                $errors[] = "Row {$rowNum}: Product name \"{$name}\" already exists.";
                continue;
            }

            try {
                Product::create([
                    'name' => $name,
                    'sku' => $sku,
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'unit_id' => $unit->id,
                    'alert_quantity' => (float)$alertRaw,
                    'note' => $note,
                    'default_purchase_price' => 0,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: Failed to import: " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }
}
