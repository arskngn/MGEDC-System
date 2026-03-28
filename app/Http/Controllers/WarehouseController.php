<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\ImportWarehousesRequest;
use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Models\GeneralSetting;
use App\Models\Warehouse;
use App\Support\CsvImportReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WarehouseController extends Controller
{
    use ResolvesCsvUploadPath;

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();

        $warehouses = Warehouse::query()
            ->withCount(['purchases', 'purchaseReturns'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('address', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('warehouses.index', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Warehouse::create([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'status' => true,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully.');
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $request->validated();
        $warehouse->update([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
        ]);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        if ($warehouse->purchases()->exists() || $warehouse->purchaseReturns()->exists()) {
            return redirect()->route('warehouses.index')->with('error', 'Cannot delete a warehouse that is still used on purchases or purchase returns.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully.');
    }

    public function toggleStatus(Warehouse $warehouse): RedirectResponse
    {
        $warehouse->update(['status' => ! $warehouse->status]);

        return redirect()->route('warehouses.index')->with(
            'success',
            $warehouse->status ? 'Warehouse has been enabled.' : 'Warehouse has been disabled.'
        );
    }

    public function importSample(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['name', 'address', 'status']);
            fputcsv($out, ['WH-10', '123 Sample Street', '1']);
            fputcsv($out, ['Warehouse One', 'Block A, Industrial Area', 'enabled']);
            fclose($out);
        }, 'warehouse.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(ImportWarehousesRequest $request): RedirectResponse
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

        $mapAddress = $map['address'] ?? null;
        $mapStatus = $map['status'] ?? null;

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

            if (Warehouse::where('name', $name)->exists()) {
                $errors[] = "Row {$rowNum}: \"{$name}\" already exists.";

                continue;
            }

            $address = null;
            if ($mapAddress !== null) {
                $addrRaw = trim((string) ($row[$mapAddress] ?? ''));
                $address = $addrRaw !== '' ? $addrRaw : null;
            }

            $status = true;
            if ($mapStatus !== null) {
                $rawStatus = strtolower(trim((string) ($row[$mapStatus] ?? '')));
                if ($rawStatus !== '') {
                    $disabledFlags = ['0', 'false', 'no', 'n', 'disabled', 'off'];
                    $enabledFlags = ['1', 'true', 'yes', 'y', 'enabled', 'on'];
                    if (in_array($rawStatus, $disabledFlags, true)) {
                        $status = false;
                    } elseif (in_array($rawStatus, $enabledFlags, true)) {
                        $status = true;
                    }
                }
            }

            try {
                Warehouse::create([
                    'name' => $name,
                    'address' => $address,
                    'status' => $status,
                ]);
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);

        if ($imported === 0 && $errors === []) {
            return back()->with('error', 'No data rows were found in the file.');
        }

        $msg = "Imported {$imported} warehouse".($imported === 1 ? '' : 's').'.';
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
