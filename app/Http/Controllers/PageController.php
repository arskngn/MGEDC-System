<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PageController extends Controller
{
    public function salesAll()
    {
        return view('pages.empty', ['title' => 'All Sales']);
    }

    public function salesReturn(Request $request)
    {
        $perPage = GeneralSetting::first()?->records_per_page ?? 15;
        $search = $request->string('search')->trim()->value();
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $salesReturns = \App\Models\SaleReturn::query()
            ->with(['sale.customer', 'warehouse'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('return_invoice_no', 'like', '%'.$search.'%')
                        ->orWhereHas('sale.customer', function ($c) use ($search) {
                            $c->where('name', 'like', '%'.$search.'%')
                                ->orWhere('phone', 'like', '%'.$search.'%');
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('return_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('return_date', '<=', $dateTo))
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('sales.return-index', ['salesReturns' => $salesReturns]);
    }

    public function customers()
    {
        return view('pages.empty', ['title' => 'Manage Customers']);
    }

    public function suppliers()
    {
        return view('pages.empty', ['title' => 'Manage Suppliers']);
    }

    public function staffAll()
    {
        return view('pages.empty', ['title' => 'All Staff']);
    }

    public function staffRoles()
    {
        return view('pages.empty', ['title' => 'Roles']);
    }

    public function transfers()
    {
        return view('pages.empty', ['title' => 'Transfers']);
    }

    public function expenseTypes()
    {
        return view('pages.empty', ['title' => 'Expense Types']);
    }

    public function expenses()
    {
        return view('pages.empty', ['title' => 'Expenses']);
    }

    public function reportSupplierPayments(Request $request)
    {
        // Redirect to the new supplier payments page
        return redirect()->route('supplier-payments.index', $request->query());
    }

    public function reportCustomerPayments(Request $request)
    {
        // Redirect to the new customer payments page
        return redirect()->route('customer-payments.index', $request->query());
    }

    public function reportStock()
    {
        return view('pages.empty', ['title' => 'Stock Report']);
    }

    public function reportDataEntryPurchases()
    {
        return view('pages.empty', ['title' => 'Data Entry: Purchases']);
    }

    public function reportDataEntryPurchaseReturn()
    {
        return view('pages.empty', ['title' => 'Data Entry: Purchase Return']);
    }

    public function reportDataEntrySales()
    {
        return view('pages.empty', ['title' => 'Data Entry: Sales']);
    }

    public function reportDataEntrySaleReturn()
    {
        return view('pages.empty', ['title' => 'Data Entry: Sale Return']);
    }

    public function reportDataEntryProducts()
    {
        return view('pages.empty', ['title' => 'Data Entry: Products']);
    }

    public function reportDataEntryCustomers()
    {
        return view('pages.empty', ['title' => 'Data Entry: Customers']);
    }

    public function reportDataEntryCustomerPayments()
    {
        return view('pages.empty', ['title' => 'Data Entry: Customer Payments']);
    }

    public function reportDataEntrySuppliers()
    {
        return view('pages.empty', ['title' => 'Data Entry: Suppliers']);
    }

    public function reportDataEntrySupplierPayments()
    {
        return view('pages.empty', ['title' => 'Data Entry: Supplier Payments']);
    }

    public function reportDataEntryAdjustments()
    {
        return view('pages.empty', ['title' => 'Data Entry: Adjustments']);
    }

    public function reportDataEntryTransfers()
    {
        return view('pages.empty', ['title' => 'Data Entry: Transfers']);
    }

    public function reportDataEntryExpenses()
    {
        return view('pages.empty', ['title' => 'Data Entry: Expenses']);
    }

    public function extraApplication()
    {
        $generalSetting = GeneralSetting::first();
        $data = [
            'title' => 'Application Information',
            'app_name' => $generalSetting->site_title ?? 'MGEDC',
            'laravel_version' => app()->version(),
            'php_version' => phpversion(),
            'timezone' => $generalSetting->timezone ?? 'UTC',
        ];

        return view('pages.extra.application', $data);
    }

    public function extraServer()
    {
        // Detect server software
        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? null;
        if (! $server_software) {
            // Try to detect from common server software headers
            $server_software = $_SERVER['HTTP_USER_AGENT'] ?? 'Not Detected';
        }

        // PHP SAPI (Server API) - shows if CLI, FPM, Apache, etc.
        $php_sapi = php_sapi_name();

        // Operating System
        $os = php_uname('s');

        // Server Address/IP
        $server_ip = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()) ?? 'Not Detected';

        // HTTP Protocol
        $server_protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'Not Detected';

        // HTTP Host
        $http_host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'Not Detected');

        // Server Port
        $server_port = $_SERVER['SERVER_PORT'] ?? 'Not Detected';

        $data = [
            'title' => 'Server Information',
            'php_version' => phpversion(),
            'php_sapi' => $php_sapi,
            'server_software' => $server_software,
            'operating_system' => $os,
            'server_ip' => $server_ip,
            'server_protocol' => $server_protocol,
            'http_host' => $http_host,
            'server_port' => $server_port,
        ];

        return view('pages.extra.server', $data);
    }

    public function extraCache()
    {
        return view('pages.extra.cache', ['title' => 'Clear System Cache']);
    }

    public function clearCache(Request $request)
    {
        try {
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('config:clear');
            Artisan::call('optimize:clear');

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'All caches cleared successfully!']);
            }

            return back()->with('success', 'All caches cleared successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Error clearing cache: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Error clearing cache: '.$e->getMessage());
        }
    }

    public function extraUpdate()
    {
        return view('pages.extra.update', ['title' => 'System Updates']);
    }

    public function reportRequest()
    {
        return view('pages.empty', ['title' => 'Report & Request']);
    }
}
