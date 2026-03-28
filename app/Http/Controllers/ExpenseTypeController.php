<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\ImportExpenseTypesRequest;
use App\Http\Requests\StoreExpenseTypeRequest;
use App\Http\Requests\UpdateExpenseTypeRequest;
use App\Models\ExpenseType;
use App\Models\GeneralSetting;
use App\Support\CsvImportReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseTypeController extends Controller
{
    use ResolvesCsvUploadPath;

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();

        $expenseTypes = ExpenseType::query()
            ->withCount('expenses')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('expense_types.index', [
            'expenseTypes' => $expenseTypes,
        ]);
    }

    public function store(StoreExpenseTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        ExpenseType::create([
            'name' => $data['name'],
        ]);

        return redirect()->route('expense_types.index')->with('success', 'Expense type created successfully.');
    }

    public function update(UpdateExpenseTypeRequest $request, ExpenseType $expenseType): RedirectResponse
    {
        $data = $request->validated();
        $expenseType->update([
            'name' => $data['name'],
        ]);

        return redirect()->route('expense_types.index')->with('success', 'Expense type updated successfully.');
    }

    public function destroy(ExpenseType $expenseType): RedirectResponse
    {
        if ($expenseType->expenses()->exists()) {
            return redirect()->route('expense_types.index')->with('error', 'Cannot delete an expense type that still has expenses assigned.');
        }

        $expenseType->delete();

        return redirect()->route('expense_types.index')->with('success', 'Expense type deleted successfully.');
    }

    public function importSample(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['name']);
            fputcsv($out, ['Sample Expense Type']);
            fclose($out);
        }, 'expense_type.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(ImportExpenseTypesRequest $request): RedirectResponse
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

            if (ExpenseType::where('name', $name)->exists()) {
                $errors[] = "Row {$rowNum}: name \"{$name}\" already exists.";

                continue;
            }

            try {
                ExpenseType::create(['name' => $name]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: {$e->getMessage()}";
            }
        }

        fclose($handle);

        $message = "Imported {$imported} expense type(s).";
        if (count($errors) > 0) {
            $message .= ' '.count($errors).' error(s): '.implode(' | ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " ... and ".(count($errors) - 5).' more.';
            }
        }

        return redirect()->route('expense_types.index')->with($errors ? 'error' : 'success', $message);
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
