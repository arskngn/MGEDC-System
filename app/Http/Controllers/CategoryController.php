<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\ImportCategoriesRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Support\CsvImportReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CategoryController extends Controller
{
    use ResolvesCsvUploadPath;

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();

        $categories = Category::query()
            ->withCount('products')
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create(['name' => $request->validated()['name']]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update(['name' => $request->validated()['name']]);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')->with('error', 'Cannot delete a category that still has products assigned. Reassign those products first.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function importSample(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['name']);
            fputcsv($out, ['Sample Category']);
            fclose($out);
        }, 'category.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(ImportCategoriesRequest $request): RedirectResponse
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

            if (Category::where('name', $name)->exists()) {
                $errors[] = "Row {$rowNum}: \"{$name}\" already exists.";

                continue;
            }

            try {
                Category::create(['name' => $name]);
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);

        if ($imported === 0 && $errors === []) {
            return back()->with('error', 'No data rows were found in the file.');
        }

        $msg = "Imported {$imported} categor".($imported === 1 ? 'y' : 'ies').'.';
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
