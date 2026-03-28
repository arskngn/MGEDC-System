<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\ImportUnitsRequest;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\GeneralSetting;
use App\Models\Unit;
use App\Support\CsvImportReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UnitController extends Controller
{
    use ResolvesCsvUploadPath;

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();

        $units = Unit::query()
            ->withCount('products')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('short_name', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('units.index', [
            'units' => $units,
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Unit::create([
            'name' => $data['name'],
            'short_name' => $data['short_name'],
        ]);

        return redirect()->route('units.index')->with('success', 'Unit created successfully.');
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $data = $request->validated();
        $unit->update([
            'name' => $data['name'],
            'short_name' => $data['short_name'],
        ]);

        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        if ($unit->products()->exists()) {
            return redirect()->route('units.index')->with('error', 'Cannot delete a unit that still has products assigned. Reassign those products first.');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit deleted successfully.');
    }

    public function importSample(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['name', 'short_name']);
            fputcsv($out, ['Sample Unit', 'su']);
            fclose($out);
        }, 'unit.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(ImportUnitsRequest $request): RedirectResponse
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

        foreach (['name', 'short_name'] as $col) {
            if (! isset($map[$col])) {
                fclose($handle);

                return back()->with('error', 'Missing required column: '.$col.'. Use the sample template.');
            }
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
            $shortName = trim((string) ($row[$map['short_name']] ?? ''));
            if ($name === '' || $shortName === '') {
                $errors[] = "Row {$rowNum}: name and short_name are required.";

                continue;
            }

            if (strlen($shortName) > 32) {
                $errors[] = "Row {$rowNum}: short_name may not be longer than 32 characters.";

                continue;
            }

            if (Unit::where('short_name', $shortName)->exists()) {
                $errors[] = "Row {$rowNum}: short_name \"{$shortName}\" already exists.";

                continue;
            }

            if (Unit::where('name', $name)->exists()) {
                $errors[] = "Row {$rowNum}: name \"{$name}\" already exists.";

                continue;
            }

            try {
                Unit::create(['name' => $name, 'short_name' => $shortName]);
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);

        if ($imported === 0 && $errors === []) {
            return back()->with('error', 'No data rows were found in the file.');
        }

        $msg = "Imported {$imported} unit(s).";
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
