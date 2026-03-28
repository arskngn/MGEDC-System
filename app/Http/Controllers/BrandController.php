<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\ImportBrandsRequest;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use App\Models\GeneralSetting;
use App\Support\CsvImportReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BrandController extends Controller
{
    use ResolvesCsvUploadPath;

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();

        $brands = Brand::query()
            ->withCount('products')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('brands.index', [
            'brands' => $brands,
        ]);
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        Brand::create(['name' => $request->validated()['name']]);

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update(['name' => $request->validated()['name']]);

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->products()->exists()) {
            return redirect()->route('brands.index')->with('error', 'Cannot delete a brand that still has products assigned. Reassign those products first.');
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }

    public function importSample(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['name']);
            fputcsv($out, ['Sample Brand']);
            fclose($out);
        }, 'brand.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(ImportBrandsRequest $request): RedirectResponse
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
            $map[strtolower(trim((string) $col))] = $i;
        }

        if (! isset($map['name'])) {
            fclose($handle);

            return back()->with('error', 'Missing required column: name. Use the sample template.');
        }

        $imported = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if ($this->csvRowIsEmpty($row)) {
                continue;
            }

            $name = trim((string) ($row[$map['name']] ?? ''));
            if ($name === '') {
                $errors[] = "Row {$rowNum}: name is required.";

                continue;
            }

            if (Brand::where('name', $name)->exists()) {
                $errors[] = "Row {$rowNum}: \"{$name}\" already exists.";

                continue;
            }

            try {
                Brand::create(['name' => $name]);
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);

        if ($imported === 0 && $errors === []) {
            return back()->with('error', 'No data rows were found in the file.');
        }

        $msg = "Imported {$imported} brand".($imported === 1 ? '' : 's').'.';
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
}
