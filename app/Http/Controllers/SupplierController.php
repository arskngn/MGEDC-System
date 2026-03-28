<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCsvUploadPath;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\GeneralSetting;
use App\Models\Supplier;
use App\Support\CsvImportReader;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierController extends Controller
{
    use ResolvesCsvUploadPath;

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;

        $suppliers = $this->filteredSuppliersQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return view('suppliers.index', ['suppliers' => $suppliers]);
    }

    public function store(StoreSupplierRequest $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        Supplier::create([
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'email' => $data['email'],
            'company_name' => $data['company_name'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validated();

        $supplier->update([
            'name' => $data['name'],
            'phone' => $data['mobile'],
            'email' => $data['email'],
            'company_name' => $data['company_name'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $suppliers = $this->filteredSuppliersQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('suppliers.pdf.all', [
            'suppliers' => $suppliers,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('suppliers-'.time().'.pdf');
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $suppliers = $this->filteredSuppliersQuery($request)->get();
        $filename = 'suppliers-'.time().'.csv';

        return response()->streamDownload(function () use ($suppliers) {
            $out = fopen('php://output', 'w');
            // Excel-compatible UTF-8 BOM
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, [
                'name',
                'email',
                'mobile',
                'company_name',
                'address',
                'payable',
                'receivable',
            ]);

            foreach ($suppliers as $s) {
                fputcsv($out, [
                    $s->name,
                    $s->email ?? '',
                    $s->phone ?? '',
                    $s->company_name ?? '',
                    $s->address ?? '',
                    (string) ($s->payable_total ?? 0),
                    (string) ($s->receivable_total ?? 0),
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importSample(): StreamedResponse
    {
        $filename = 'supplier.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['name', 'email', 'mobile', 'company_name', 'address']);
            fputcsv($out, ['Sample Supplier', 'supplier@example.com', '+14155551234', 'Sample Company', 'Sample Address']);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importStore(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt,text/plain,text/csv'],
        ]);

        $file = $request->file('csv_file');
        $path = $this->csvUploadPath($file);

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

        $required = ['name', 'email', 'mobile'];
        foreach ($required as $col) {
            if (! isset($map[$col])) {
                fclose($handle);

                return back()->with('error', 'Missing required column: '.$col.'. Use the sample template.');
            }
        }

        $rowNum = 1;
        $imported = 0;
        $skipped = 0;
        $firstError = null;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNum++;
            if ($this->csvRowIsEmpty($row)) {
                continue;
            }

            $name = trim((string) ($row[$map['name']] ?? ''));
            $email = trim((string) ($row[$map['email']] ?? ''));
            $mobile = trim((string) ($row[$map['mobile']] ?? ''));

            $companyName = isset($map['company_name']) ? trim((string) ($row[$map['company_name']] ?? '')) : null;
            $address = isset($map['address']) ? trim((string) ($row[$map['address']] ?? '')) : null;

            if ($name === '' || $email === '' || $mobile === '') {
                $skipped++;
                $firstError ??= 'Row '.$rowNum.': name, email, and mobile are required.';
                continue;
            }

            $exists = Supplier::query()
                ->where('email', $email)
                ->orWhere('phone', $mobile)
                ->exists();

            if ($exists) {
                $skipped++;
                $firstError ??= 'Row '.$rowNum.': supplier with the same email or mobile already exists.';
                continue;
            }

            try {
                Supplier::create([
                    'name' => $name,
                    'phone' => $mobile,
                    'email' => $email,
                    'company_name' => $companyName !== '' ? $companyName : null,
                    'address' => $address !== '' ? $address : null,
                ]);
                $imported++;
            } catch (\Throwable $e) {
                $skipped++;
                $firstError ??= 'Row '.$rowNum.': failed to import ('.$e->getMessage().').';
            }
        }

        fclose($handle);

        if ($skipped > 0) {
            return redirect()->route('suppliers.index')->with('error', 'Imported '.$imported.' suppliers. Skipped '.$skipped.' rows.'.($firstError ? ' '.$firstError : ''));
        }

        return redirect()->route('suppliers.index')->with('success', 'Suppliers imported successfully.');
    }

    private function filteredSuppliersQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();

        $payableSub = DB::table('purchases')
            ->select(
                'supplier_id',
                DB::raw('SUM(CASE WHEN payable_amount - paid_amount > 0.009 THEN payable_amount - paid_amount ELSE 0 END) as payable_total')
            )
            ->groupBy('supplier_id');

        $receivableSub = DB::table('purchase_returns')
            ->join('purchases', 'purchases.id', '=', 'purchase_returns.purchase_id')
            ->select(
                'purchases.supplier_id',
                DB::raw('SUM(CASE WHEN purchase_returns.receivable_amount - purchase_returns.received_amount > 0.009 THEN purchase_returns.receivable_amount - purchase_returns.received_amount ELSE 0 END) as receivable_total')
            )
            ->groupBy('purchases.supplier_id');

        return Supplier::query()
            ->leftJoinSub($payableSub, 'p', 'p.supplier_id', '=', 'suppliers.id')
            ->leftJoinSub($receivableSub, 'r', 'r.supplier_id', '=', 'suppliers.id')
            ->select(
                'suppliers.*',
                DB::raw('COALESCE(p.payable_total, 0) as payable_total'),
                DB::raw('COALESCE(r.receivable_total, 0) as receivable_total')
            )
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('suppliers.name', 'like', '%'.$search.'%')
                        ->orWhere('suppliers.phone', 'like', '%'.$search.'%')
                        ->orWhere('suppliers.email', 'like', '%'.$search.'%')
                        ->orWhere('suppliers.company_name', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('suppliers.name');
    }

    private function csvRowIsEmpty(?array $row): bool
    {
        if (! is_array($row) || $row === []) {
            return true;
        }

        foreach ($row as $cell) {
            if (is_string($cell) && trim($cell) !== '') {
                return false;
            }
        }

        return true;
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

