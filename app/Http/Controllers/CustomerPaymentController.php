<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\SaleReturnPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerPaymentController extends Controller
{
    public function index(Customer $customer): View
    {
        $receivableSales = Sale::query()
            ->where('customer_id', $customer->id)
            ->whereRaw('receivable_amount - paid_amount > 0.009')
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->get();

        $receivableTotal = (float) $receivableSales->sum(fn (Sale $s) => (float) $s->due_amount);

        // Use DB query to avoid IDE/type-analysis issues while scaffolding this module.
        $payableReturns = DB::table('sale_returns')
            ->join('sales', 'sales.id', '=', 'sale_returns.sale_id')
            ->select([
                'sale_returns.id',
                'sale_returns.return_invoice_no',
                'sale_returns.return_date',
                'sale_returns.payable_amount',
                'sale_returns.paid_amount',
                'sale_returns.created_at',
            ])
            ->where('sales.customer_id', $customer->id)
            ->whereRaw('sale_returns.payable_amount - sale_returns.paid_amount > 0.009')
            ->orderByDesc('sale_returns.return_date')
            ->orderByDesc('sale_returns.id')
            ->get();

        $payableTotal = (float) $payableReturns->sum(function ($r) {
            return max(0, (float) $r->payable_amount - (float) $r->paid_amount);
        });

        $lines = collect();

        foreach ($receivableSales as $s) {
            $lines->push([
                'reason' => 'Sale',
                'invoice_no' => $s->invoice_no,
                'amount' => (float) $s->due_amount,
                'date' => $s->sale_date ? strtotime((string) $s->sale_date) : strtotime((string) $s->created_at),
            ]);
        }

        foreach ($payableReturns as $r) {
            $lines->push([
                'reason' => 'Sale Return',
                'invoice_no' => $r->return_invoice_no,
                'amount' => max(0, (float) $r->payable_amount - (float) $r->paid_amount),
                'date' => $r->return_date ? strtotime((string) $r->return_date) : strtotime((string) $r->created_at),
            ]);
        }

        $lines = $lines->sortByDesc('date')->values()->all();

        return view('customers.payments', [
            'customer' => $customer,
            'receivableTotal' => $receivableTotal,
            'payableTotal' => $payableTotal,
            'lines' => $lines,
        ]);
    }

    public function clear(Request $request, Customer $customer)
    {
        $user = $request->user();

        $receivableSales = Sale::query()
            ->where('customer_id', $customer->id)
            ->whereRaw('receivable_amount - paid_amount > 0.009')
            ->get();

        $payableReturns = DB::table('sale_returns')
            ->join('sales', 'sales.id', '=', 'sale_returns.sale_id')
            ->select([
                'sale_returns.id',
                'sale_returns.payable_amount',
                'sale_returns.paid_amount',
            ])
            ->where('sales.customer_id', $customer->id)
            ->whereRaw('sale_returns.payable_amount - sale_returns.paid_amount > 0.009')
            ->get();

        if ($receivableSales->isEmpty() && $payableReturns->isEmpty()) {
            return redirect()->route('customers.payments.index', $customer)->with('success', 'No unsettled payment found for this customer.');
        }

        DB::transaction(function () use ($user, $customer, $receivableSales, $payableReturns) {
            if ($user && $user->hasPermission('Store Customer Payment') && $receivableSales->isNotEmpty()) {
                foreach ($receivableSales as $s) {
                    $due = (float) $s->due_amount;
                    if ($due <= 0.0001) {
                        continue;
                    }

                    SalePayment::create([
                        'sale_id' => $s->id,
                        'user_id' => $user->id,
                        'amount' => $due,
                    ]);

                    Sale::where('id', $s->id)->increment('paid_amount', $due);
                }
            }

            if ($user && $user->hasPermission('Store Payable Payment Of Customer') && $payableReturns->isNotEmpty()) {
                foreach ($payableReturns as $r) {
                    $due = max(0, (float) $r->payable_amount - (float) $r->paid_amount);
                    if ($due <= 0.0001) {
                        continue;
                    }

                    SaleReturnPayment::create([
                        'sale_return_id' => $r->id,
                        'user_id' => $user->id,
                        'amount' => $due,
                        'paid_at' => now(),
                    ]);

                    DB::table('sale_returns')->where('id', $r->id)->increment('paid_amount', $due);
                }
            }
        });

        return redirect()->route('customers.payments.index', $customer)->with('success', 'Payments cleared successfully.');
    }

    public function allPayments(Request $request): View
    {
        $query = $request->get('search', '');
        $filter = $request->get('filter', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get Sale Payments (Received from customers)
        $salePaymentsQuery = SalePayment::with(['sale.customer', 'user'])
            ->when($query, function ($q, $search) {
                $q->whereHas('sale.customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            });

        // Only include sale payments if filter is 'all' or 'received_from_customer'
        $salePayments = collect();
        if ($filter === 'all' || $filter === 'received_from_customer') {
            $salePayments = $salePaymentsQuery->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_no' => $payment->sale->invoice_no ?? 'N/A',
                    'date' => $payment->created_at,
                    'customer' => $payment->sale->customer,
                    'trx' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'reason' => 'Received From Customer',
                    'amount' => (float) $payment->amount,
                    'type' => 'receipt',
                    'user' => $payment->user
                ];
            });
        }

        // Get Sale Return Payments (Paid to customers)
        $returnPaymentsQuery = SaleReturnPayment::with(['saleReturn.sale.customer', 'user'])
            ->when($query, function ($q, $search) {
                $q->whereHas('saleReturn.sale.customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('saleReturn', function ($sq) use ($search) {
                    $sq->where('return_invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('paid_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('paid_at', '<=', $date);
            });

        // Only include return payments if filter is 'all' or 'paid_for_sale_return'
        $returnPayments = collect();
        if ($filter === 'all' || $filter === 'paid_for_sale_return') {
            $returnPayments = $returnPaymentsQuery->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_no' => $payment->saleReturn->return_invoice_no ?? 'N/A',
                    'date' => $payment->paid_at ?? $payment->created_at,
                    'customer' => $payment->saleReturn->sale->customer,
                    'trx' => 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'reason' => 'Paid For Sale Return',
                    'amount' => (float) $payment->amount,
                    'type' => 'payment',
                    'user' => $payment->user
                ];
            });
        }

        // Merge and sort
        $allPayments = $salePayments->merge($returnPayments)
            ->sortByDesc('date')
            ->values();

        return view('customer-payments.index', [
            'payments' => $allPayments,
            'search' => $query,
            'filter' => $filter,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $query = $request->get('search', '');
        $filter = $request->get('filter', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get Sale Payments (Received from customers)
        $salePaymentsQuery = SalePayment::with(['sale.customer', 'user'])
            ->when($query, function ($q, $search) {
                $q->whereHas('sale.customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            });

        // Only include sale payments if filter is 'all' or 'received_from_customer'
        $salePayments = collect();
        if ($filter === 'all' || $filter === 'received_from_customer') {
            $salePayments = $salePaymentsQuery->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_no' => $payment->sale->invoice_no ?? 'N/A',
                    'date' => $payment->created_at,
                    'customer' => $payment->sale->customer,
                    'trx' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'reason' => 'Received From Customer',
                    'amount' => (float) $payment->amount,
                    'type' => 'receipt',
                    'user' => $payment->user
                ];
            });
        }

        // Get Sale Return Payments (Paid to customers)
        $returnPaymentsQuery = SaleReturnPayment::with(['saleReturn.sale.customer', 'user'])
            ->when($query, function ($q, $search) {
                $q->whereHas('saleReturn.sale.customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('saleReturn', function ($sq) use ($search) {
                    $sq->where('return_invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('paid_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('paid_at', '<=', $date);
            });

        // Only include return payments if filter is 'all' or 'paid_for_sale_return'
        $returnPayments = collect();
        if ($filter === 'all' || $filter === 'paid_for_sale_return') {
            $returnPayments = $returnPaymentsQuery->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_no' => $payment->saleReturn->return_invoice_no ?? 'N/A',
                    'date' => $payment->paid_at ?? $payment->created_at,
                    'customer' => $payment->saleReturn->sale->customer,
                    'trx' => 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'reason' => 'Paid For Sale Return',
                    'amount' => (float) $payment->amount,
                    'type' => 'payment',
                    'user' => $payment->user
                ];
            });
        }

        // Merge and sort
        $allPayments = $salePayments->merge($returnPayments)
            ->sortByDesc('date')
            ->values();

        $pdf = \PDF::loadView('customer-payments.pdf', [
            'payments' => $allPayments,
            'search' => $query,
            'filter' => $filter,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        return $pdf->download('customer-payments-' . date('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $request): Response
    {
        $query = $request->get('search', '');
        $filter = $request->get('filter', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get Sale Payments (Received from customers)
        $salePaymentsQuery = SalePayment::with(['sale.customer', 'user'])
            ->when($query, function ($q, $search) {
                $q->whereHas('sale.customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('sale', function ($sq) use ($search) {
                    $sq->where('invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            });

        // Only include sale payments if filter is 'all' or 'received_from_customer'
        $salePayments = collect();
        if ($filter === 'all' || $filter === 'received_from_customer') {
            $salePayments = $salePaymentsQuery->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_no' => $payment->sale->invoice_no ?? 'N/A',
                    'date' => $payment->created_at,
                    'customer' => $payment->sale->customer,
                    'trx' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'reason' => 'Received From Customer',
                    'amount' => (float) $payment->amount,
                    'type' => 'receipt',
                    'user' => $payment->user
                ];
            });
        }

        // Get Sale Return Payments (Paid to customers)
        $returnPaymentsQuery = SaleReturnPayment::with(['saleReturn.sale.customer', 'user'])
            ->when($query, function ($q, $search) {
                $q->whereHas('saleReturn.sale.customer', function ($cq) use ($search) {
                    $cq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('saleReturn', function ($sq) use ($search) {
                    $sq->where('return_invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('paid_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('paid_at', '<=', $date);
            });

        // Only include return payments if filter is 'all' or 'paid_for_sale_return'
        $returnPayments = collect();
        if ($filter === 'all' || $filter === 'paid_for_sale_return') {
            $returnPayments = $returnPaymentsQuery->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_no' => $payment->saleReturn->return_invoice_no ?? 'N/A',
                    'date' => $payment->paid_at ?? $payment->created_at,
                    'customer' => $payment->saleReturn->sale->customer,
                    'trx' => 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'reason' => 'Paid For Sale Return',
                    'amount' => (float) $payment->amount,
                    'type' => 'payment',
                    'user' => $payment->user
                ];
            });
        }

        // Merge and sort
        $allPayments = $salePayments->merge($returnPayments)
            ->sortByDesc('date')
            ->values();

        $filename = 'customer-payments-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($allPayments) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, ['S.N.', 'Invoice No.', 'Date', 'Customer', 'TRX', 'Reason', 'Amount']);
            
            // CSV Data
            foreach ($allPayments as $index => $payment) {
                fputcsv($file, [
                    $index + 1,
                    $payment['invoice_no'],
                    $payment['date']->format('Y-m-d'),
                    $payment['customer']->name ?? 'N/A',
                    $payment['trx'],
                    $payment['reason'],
                    formatCurrency($payment['amount'])
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

