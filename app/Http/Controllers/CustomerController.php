<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Models\GeneralSetting;
use App\Services\CustomerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    use \App\Http\Controllers\Concerns\ResolvesCsvUploadPath;

    public function __construct(protected CustomerService $customerService)
    {
    }

    public function index(Request $request): View
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;

        $customers = $this->filteredCustomersQuery($request)
            ->paginate($perPage)
            ->withQueryString();

        return view('customers.index', ['customers' => $customers]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $this->customerService->createCustomer($request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $this->customerService->updateCustomer($customer, $request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function exportPdfAll(Request $request)
    {
        $customers = $this->filteredCustomersQuery($request)->get();
        $generalSetting = GeneralSetting::first();

        $pdf = Pdf::loadView('customers.pdf.all', [
            'customers' => $customers,
            'generalSetting' => $generalSetting,
            'logoSrc' => $this->pdfLogoDataUri($generalSetting),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('customers-'.time().'.pdf');
    }

    public function exportCsvAll(Request $request): StreamedResponse
    {
        $customers = $this->filteredCustomersQuery($request)->get();

        return $this->customerService->exportToCsv($customers);
    }

    public function importSample(): StreamedResponse
    {
        $filename = 'customer.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['name', 'email', 'mobile', 'address']);
            fputcsv($out, ['Sample Customer', 'customer@example.com', '+14155551234', 'Sample Address']);

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt,text/plain,text/csv'],
        ]);

        $file = $request->file('csv_file');
        $path = $this->csvUploadPath($file);

        $result = $this->customerService->importFromCsv($path);

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        if ($result['skipped'] > 0) {
            $msg = 'Imported ' . $result['imported'] . ' customers. Skipped ' . $result['skipped'] . ' rows.';
            if ($result['firstError']) {
                $msg .= ' ' . $result['firstError'];
            }
            return redirect()->route('customers.index')->with('error', $msg);
        }

        return redirect()->route('customers.index')->with('success', 'Customers imported successfully.');
    }

    private function filteredCustomersQuery(Request $request)
    {
        $search = $request->string('search')->trim()->value();

        $receivableSub = DB::table('sales')
            ->select(
                'customer_id',
                DB::raw('SUM(CASE WHEN receivable_amount - paid_amount > 0.009 THEN receivable_amount - paid_amount ELSE 0 END) as receivable_total')
            )
            ->groupBy('customer_id');

        $payableSub = DB::table('sale_returns')
            ->join('sales', 'sales.id', '=', 'sale_returns.sale_id')
            ->select(
                'sales.customer_id',
                DB::raw('SUM(CASE WHEN sale_returns.payable_amount - sale_returns.paid_amount > 0.009 THEN sale_returns.payable_amount - sale_returns.paid_amount ELSE 0 END) as payable_total')
            )
            ->groupBy('sales.customer_id');

        return Customer::query()
            ->leftJoinSub($receivableSub, 'r', 'r.customer_id', '=', 'customers.id')
            ->leftJoinSub($payableSub, 'p', 'p.customer_id', '=', 'customers.id')
            ->select(
                'customers.*',
                DB::raw('COALESCE(r.receivable_total, 0) as receivable_total'),
                DB::raw('COALESCE(p.payable_total, 0) as payable_total')
            )
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('customers.name', 'like', '%'.$search.'%')
                        ->orWhere('customers.phone', 'like', '%'.$search.'%')
                        ->orWhere('customers.email', 'like', '%'.$search.'%')
                        ->orWhere('customers.address', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('customers.name');
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

