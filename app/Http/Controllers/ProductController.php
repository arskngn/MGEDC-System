<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\ImportProductsRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\Unit;
use App\Support\CsvImportReader;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    use ResolvesCsvUploadPath;

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $products = $this->filteredProductsQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        return view('products.create', [
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        Product::create([
            'name' => $request->validated()['name'],
            'category_id' => $request->validated()['category_id'],
            'brand_id' => $request->validated()['brand_id'],
            'sku' => $request->validated()['sku'],
            'unit_id' => $request->validated()['unit_id'],
            'alert_quantity' => $request->validated()['alert_quantity'],
            'note' => $request->validated()['note'] ?? null,
            'default_purchase_price' => 0,
        ]);

        return redirect()->route('products.all')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'units' => Unit::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update([
            'name' => $request->validated()['name'],
            'category_id' => $request->validated()['category_id'],
            'brand_id' => $request->validated()['brand_id'],
            'sku' => $request->validated()['sku'],
            'unit_id' => $request->validated()['unit_id'],
            'alert_quantity' => $request->validated()['alert_quantity'],
            'note' => $request->validated()['note'] ?? null,
        ]);

        return redirect()->route('products.all')->with('success', 'Product updated successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $products = $this->filteredProductsQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('products.pdf.all', [
            'products' => $products,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('products-'.time().'.pdf');
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $products = $this->filteredProductsQuery($request)->get();
        $filename = 'products-'.time().'.csv';

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, [
                'SKU',
                'Name',
                'Category',
                'Brand',
                'Stock',
                'Unit',
                'Total Sale',
                'Alert Qty',
                'Note',
            ]);
            foreach ($products as $p) {
                fputcsv($out, [
                    $p->sku,
                    $p->name,
                    $p->category?->name ?? '',
                    $p->brand?->name ?? '',
                    rtrim(rtrim(number_format((float) $p->current_stock, 4, '.', ''), '0'), '.') ?: '0',
                    $p->unit->short_name ?? $p->unit->name,
                    (string) $p->total_sold,
                    rtrim(rtrim(number_format((float) $p->alert_quantity, 4, '.', ''), '0'), '.') ?: '0',
                    $p->note ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importSample(): StreamedResponse
    {
        $filename = 'product.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['name', 'category', 'sku', 'brand', 'unit', 'alert_quantity', 'note']);
            fputcsv($out, ['Sample Product', 'Electronics', 'SKU001', 'Panasonic', 'piece', '10', '']);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(ImportProductsRequest $request): RedirectResponse
    {
        $path = $this->csvUploadPath($request->file('csv_file'));
        $opened = CsvImportReader::open($path);
        if ($opened === null) {
            return back()->with('error', 'Could not read the uploaded file or the CSV is empty.');
        }

        $handle = $opened['handle'];
        $delimiter = $opened['delimiter'];
        $header = $opened['header'];

        $map = [];
        foreach ($header as $i => $col) {
            $key = strtolower(trim((string) $col));
            $map[$key] = $i;
        }

        $required = ['name', 'category', 'sku', 'brand', 'unit', 'alert_quantity'];
        foreach ($required as $col) {
            if (! isset($map[$col])) {
                fclose($handle);

                return back()->with('error', 'Missing required column: '.$col.'. Use the sample template.');
            }
        }

        $categories = Category::all()->keyBy(fn ($c) => strtolower($c->name));
        $brands = Brand::all()->keyBy(fn ($b) => strtolower($b->name));
        $units = Unit::all();
        $unitByShort = $units->keyBy(fn ($u) => strtolower($u->short_name));
        $unitByName = $units->keyBy(fn ($u) => strtolower($u->name));

        $rowNum = 1;
        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if ($this->csvRowIsEmpty($row)) {
                continue;
            }

            $name = trim((string) ($row[$map['name']] ?? ''));
            $sku = trim((string) ($row[$map['sku']] ?? ''));
            if ($name === '' && $sku === '') {
                continue;
            }

            $categoryName = strtolower(trim((string) ($row[$map['category']] ?? '')));
            $brandName = strtolower(trim((string) ($row[$map['brand']] ?? '')));
            $unitKey = strtolower(trim((string) ($row[$map['unit']] ?? '')));
            $alertRaw = trim((string) ($row[$map['alert_quantity']] ?? ''));
            $note = isset($map['note']) ? trim((string) ($row[$map['note']] ?? '')) : '';

            if ($name === '' || $sku === '' || $categoryName === '' || $brandName === '' || $unitKey === '' || $alertRaw === '') {
                $errors[] = "Row {$rowNum}: All of name, category, sku, brand, unit, and alert_quantity are required.";

                continue;
            }

            if (! is_numeric($alertRaw) || (float) $alertRaw < 0) {
                $errors[] = "Row {$rowNum}: alert_quantity must be a non-negative number.";

                continue;
            }

            $category = $categories->get($categoryName);
            if (! $category) {
                $errors[] = "Row {$rowNum}: Unknown category \"{$row[$map['category']]}\".";

                continue;
            }

            $brand = $brands->get($brandName);
            if (! $brand) {
                $errors[] = "Row {$rowNum}: Unknown brand \"{$row[$map['brand']]}\".";

                continue;
            }

            $unit = $unitByShort->get($unitKey) ?? $unitByName->get($unitKey);
            if (! $unit) {
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
                DB::transaction(function () use ($name, $sku, $category, $brand, $unit, $alertRaw, $note) {
                    Product::create([
                        'name' => $name,
                        'sku' => $sku,
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'unit_id' => $unit->id,
                        'alert_quantity' => $alertRaw,
                        'note' => $note !== '' ? $note : null,
                        'default_purchase_price' => 0,
                    ]);
                });
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);

        if ($imported === 0 && $errors === []) {
            return back()->with('error', 'No data rows were found in the file.');
        }

        $msg = "Imported {$imported} product(s).";
        if ($errors !== []) {
            $msg .= ' '.count($errors).' row(s) skipped: '.implode(' ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $msg .= ' …';
            }
        }

        return back()->with('success', $msg);
    }

    private function csvRowIsEmpty(?array $row): bool
    {
        if ($row === null || $row === []) {
            return true;
        }
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private function filteredProductsQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();

        return Product::query()
            ->with(['category', 'brand', 'unit'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('brand', fn ($bq) => $bq->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('unit', function ($uq) use ($search) {
                            $uq->where('name', 'like', '%'.$search.'%')
                                ->orWhere('short_name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->orderBy('name');
    }

    private function pdfLogoDataUri(?GeneralSetting $generalSetting): ?string
    {
        if (! $generalSetting || ! $generalSetting->logo_light) {
            return null;
        }
        $path = public_path($generalSetting->logo_light);
        if (! is_readable($path)) {
            return null;
        }
        $data = @file_get_contents($path);
        if ($data === false) {
            return null;
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };

        return 'data:'.$mime.';base64,'.base64_encode($data);
    }
}
