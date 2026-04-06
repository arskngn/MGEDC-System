<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Unit;
use App\Services\ImportExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $filePath)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(ImportExportService $importExportService): void
    {
        $opened = $importExportService->openCsv($this->filePath);
        if (!$opened) {
            Log::error("Job Failed: Could not open CSV file at {$this->filePath}");
            return;
        }

        $handle = $opened['handle'];
        $delimiter = $opened['delimiter'];
        $map = $opened['map'];

        $categories = Category::all()->keyBy(fn($c) => strtolower($c->name));
        $brands = Brand::all()->keyBy(fn($b) => strtolower($b->name));
        $units = Unit::all();
        $unitByShort = $units->keyBy(fn($u) => strtolower($u->short_name));
        $unitByName = $units->keyBy(fn($u) => strtolower($u->name));

        $imported = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if ($importExportService->isRowEmpty($row)) continue;

            $name = trim((string)($row[$map['name']] ?? ''));
            $sku = trim((string)($row[$map['sku']] ?? ''));
            if ($name === '' || $sku === '') continue;

            $categoryName = strtolower(trim((string)($row[$map['category']] ?? '')));
            $brandName = strtolower(trim((string)($row[$map['brand']] ?? '')));
            $unitKey = strtolower(trim((string)($row[$map['unit']] ?? '')));
            $alertRaw = trim((string)($row[$map['alert_quantity']] ?? ''));
            $note = isset($map['note']) ? trim((string)($row[$map['note']] ?? '')) : '';

            $category = $categories->get($categoryName);
            $brand = $brands->get($brandName);
            $unit = $unitByShort->get($unitKey) ?? $unitByName->get($unitKey);

            if (!$category || !$brand || !$unit || Product::where('sku', $sku)->exists()) {
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
                Log::error("Import Row {$rowNum} Error: " . $e->getMessage());
            }
        }

        fclose($handle);
        // Clean up the temporary file
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }

        Log::info("Background Job Completed: Imported {$imported} products from {$this->filePath}");
    }
}
