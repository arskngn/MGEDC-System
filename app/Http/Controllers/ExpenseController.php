<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\ImportExpensesRequest;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\GeneralSetting;
use App\Support\CsvImportReader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    use ResolvesCsvUploadPath;

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $expenses = Expense::query()
            ->with('expenseType')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('description', 'like', '%'.$search.'%')
                        ->orWhereHas('expenseType', function ($s) use ($search) {
                            $s->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('date', '<=', $dateTo))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $expenseTypes = ExpenseType::orderBy('name')->get();

        return view('expenses.index', [
            'expenses' => $expenses,
            'expenseTypes' => $expenseTypes,
        ]);
    }

    public function create(): View
    {
        $expenseTypes = ExpenseType::orderBy('name')->get();

        return view('expenses.create', [
            'expenseTypes' => $expenseTypes,
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Expense::create([
            'expense_type_id' => $data['expense_type_id'],
            'date' => $data['date'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense created successfully.');
    }

    public function edit(Expense $expense): View
    {
        $expenseTypes = ExpenseType::orderBy('name')->get();

        return view('expenses.edit', [
            'expense' => $expense,
            'expenseTypes' => $expenseTypes,
        ]);
    }

    public function show(Expense $expense)
    {
        return response()->json([
            'id' => $expense->id,
            'expense_type_id' => $expense->expense_type_id,
            'date' => $expense->date->format('Y-m-d'),
            'amount' => $expense->amount,
            'description' => $expense->description,
        ]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $data = $request->validated();
        $expense->update([
            'expense_type_id' => $data['expense_type_id'],
            'date' => $data['date'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $expenses = Expense::query()
            ->with('expenseType')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('description', 'like', '%'.$search.'%')
                        ->orWhereHas('expenseType', function ($s) use ($search) {
                            $s->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('date', '<=', $dateTo))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('expenses.pdf.all', [
            'expenses' => $expenses,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('all-expenses-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function exportCsvAll(Request $request)
    {
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $expenses = Expense::query()
            ->with('expenseType')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('description', 'like', '%'.$search.'%')
                        ->orWhereHas('expenseType', function ($s) use ($search) {
                            $s->where('name', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('date', '<=', $dateTo))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $filename = 'expenses-'.now()->format('Y-m-d-His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['S.N.', 'Type', 'Date', 'Amount', 'Note']);

            $generalSetting = GeneralSetting::first();
            $currencySymbol = $generalSetting?->currency_symbol ?? '$';

            foreach ($expenses as $i => $expense) {
                fputcsv($file, [
                    $i + 1,
                    $expense->expenseType->name,
                    $expense->date->format('d M, Y'),
                    $currencySymbol.number_format($expense->amount, 2),
                    $expense->description ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($data);
    }

    public function importSample(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['expense_type', 'date_of_expense', 'amount', 'note']);
            fputcsv($out, ['Office Utilities', '2024-01-15', '500.00', 'Monthly utility bill']);
            fclose($out);
        }, 'expense.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function importStore(ImportExpensesRequest $request): RedirectResponse
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

        if (! isset($map['expense_type']) || ! isset($map['date_of_expense']) || ! isset($map['amount'])) {
            fclose($handle);

            return back()->with('error', 'Missing required columns. Use the sample template.');
        }

        $imported = 0;
        $errors = [];
        $rowNum = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if ($this->csvRowIsEmpty($row)) {
                continue;
            }

            $expenseType = trim((string) ($row[$map['expense_type']] ?? ''));
            $dateOfExpense = trim((string) ($row[$map['date_of_expense']] ?? ''));
            $amount = trim((string) ($row[$map['amount']] ?? ''));
            $note = isset($map['note']) ? trim((string) ($row[$map['note']] ?? '')) : '';

            if ($expenseType === '') {
                $errors[] = "Row {$rowNum}: expense_type is required.";
                continue;
            }
            if ($dateOfExpense === '') {
                $errors[] = "Row {$rowNum}: date_of_expense is required.";
                continue;
            }
            if ($amount === '') {
                $errors[] = "Row {$rowNum}: amount is required.";
                continue;
            }

            $type = ExpenseType::where('name', $expenseType)->first();
            if (! $type) {
                $errors[] = "Row {$rowNum}: expense_type '{$expenseType}' does not exist.";
                continue;
            }

            if (! is_numeric($amount)) {
                $errors[] = "Row {$rowNum}: amount must be a valid number.";
                continue;
            }

            try {
                $date = \Carbon\Carbon::parse($dateOfExpense)->format('Y-m-d');
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: Invalid date format for '{$dateOfExpense}'.";
                continue;
            }

            try {
                Expense::create([
                    'expense_type_id' => $type->id,
                    'date' => $date,
                    'amount' => (float) $amount,
                    'description' => $note ?: null,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: Failed to create expense - {$e->getMessage()}";
                continue;
            }
        }

        fclose($handle);

        if ($errors) {
            $errorFilename = 'expenses-error-'.now()->format('Y-m-d-His').'.csv';
            $errorHeaders = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"$errorFilename\"",
            ];

            session()->flash('error', count($errors) > 0 ? implode(' | ', array_slice($errors, 0, 3)).' and more...' : 'No errors');

            return back()->with('error', "Imported {$imported} expense(s). ".count($errors).' error(s) found.');
        }

        return redirect()->route('expenses.index')->with('success', "Successfully imported {$imported} expense(s).");
    }

    protected function csvRowIsEmpty(array $row): bool
    {
        return empty(array_filter($row, fn ($value) => null !== $value && '' !== trim((string) $value)));
    }
}
