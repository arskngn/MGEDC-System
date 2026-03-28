<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnPayment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierPaymentController extends Controller
{
    public function index(Supplier $supplier): View
    {
        $payablePurchases = Purchase::query()
            ->where('supplier_id', $supplier->id)
            ->whereRaw('payable_amount - paid_amount > 0.009')
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();

        $receivableReturns = PurchaseReturn::query()
            ->whereRaw('receivable_amount - received_amount > 0.009')
            ->whereHas('purchase', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->orderByDesc('return_date')
            ->orderByDesc('id')
            ->get();

        $payableTotal = (float) $payablePurchases->sum(fn (Purchase $p) => (float) $p->due_amount);
        $receivableTotal = (float) $receivableReturns->sum(fn (PurchaseReturn $r) => (float) $r->due_amount);

        $lines = collect();

        foreach ($payablePurchases as $p) {
            $lines->push([
                'reason' => 'Purchase',
                'invoice_no' => $p->invoice_no,
                'amount' => (float) $p->due_amount,
                'date' => $p->purchase_date ? strtotime((string) $p->purchase_date) : strtotime((string) $p->created_at),
            ]);
        }

        foreach ($receivableReturns as $r) {
            $lines->push([
                'reason' => 'Purchase Return',
                'invoice_no' => $r->return_invoice_no,
                'amount' => (float) $r->due_amount,
                'date' => $r->return_date ? strtotime((string) $r->return_date) : strtotime((string) $r->created_at),
            ]);
        }

        $lines = $lines->sortByDesc('date')->values()->all();

        return view('suppliers.payments', [
            'supplier' => $supplier,
            'payableTotal' => $payableTotal,
            'receivableTotal' => $receivableTotal,
            'lines' => $lines,
        ]);
    }

    public function clear(Request $request, Supplier $supplier)
    {
        $user = $request->user();

        $payablePurchases = Purchase::query()
            ->where('supplier_id', $supplier->id)
            ->whereRaw('payable_amount - paid_amount > 0.009')
            ->get();

        $receivableReturns = PurchaseReturn::query()
            ->whereRaw('receivable_amount - received_amount > 0.009')
            ->whereHas('purchase', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->get();

        if ($payablePurchases->isEmpty() && $receivableReturns->isEmpty()) {
            return redirect()
                ->route('suppliers.payments.index', $supplier)
                ->with('success', 'No unsettled payment found for this supplier.');
        }

        DB::transaction(function () use ($user, $supplier, $payablePurchases, $receivableReturns) {
            if ($user && $user->hasPermission('Store Supplier Payment') && $payablePurchases->isNotEmpty()) {
                foreach ($payablePurchases as $p) {
                    $due = (float) $p->due_amount;
                    if ($due <= 0.0001) {
                        continue;
                    }

                    PurchasePayment::create([
                        'purchase_id' => $p->id,
                        'user_id' => $user->id,
                        'amount' => $due,
                    ]);

                    Purchase::where('id', $p->id)->increment('paid_amount', $due);
                }
            }

            if ($user && $user->hasPermission('Store Supplier Payment Receive') && $receivableReturns->isNotEmpty()) {
                foreach ($receivableReturns as $r) {
                    $due = (float) $r->due_amount;
                    if ($due <= 0.0001) {
                        continue;
                    }

                    PurchaseReturnPayment::create([
                        'purchase_return_id' => $r->id,
                        'user_id' => $user->id,
                        'amount' => $due,
                        'paid_at' => now(),
                    ]);

                    PurchaseReturn::where('id', $r->id)->increment('received_amount', $due);
                }
            }
        });

        return redirect()->route('suppliers.payments.index', $supplier)->with('success', 'Payments cleared successfully.');
    }

    public function allPayments(Request $request): View
    {
        $query = $request->get('search', '');
        $filter = $request->get('filter', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Get Purchase Payments
        $purchasePaymentsQuery = PurchasePayment::with(['purchase.supplier', 'user'])
            ->when($query, function ($q, $search) {
                $q->whereHas('purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchase', function ($pq) use ($search) {
                    $pq->where('invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            });

        // Only include purchase payments if filter is 'all' or 'paid_for_purchase'
        $purchasePayments = collect();
        if ($filter === 'all' || $filter === 'paid_for_purchase') {
            $purchasePayments = $purchasePaymentsQuery->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_no' => $payment->purchase->invoice_no ?? 'N/A',
                    'date' => $payment->created_at,
                    'supplier' => $payment->purchase->supplier,
                    'trx' => 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'reason' => 'Paid For Purchase',
                    'amount' => (float) $payment->amount,
                    'type' => 'payment',
                    'user' => $payment->user
                ];
            });
        }

        // Get Purchase Return Payments (Received)
        $returnPaymentsQuery = PurchaseReturnPayment::with(['purchaseReturn.purchase.supplier', 'user'])
            ->when($query, function ($q, $search) {
                $q->whereHas('purchaseReturn.purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchaseReturn', function ($pq) use ($search) {
                    $pq->where('return_invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('paid_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('paid_at', '<=', $date);
            });

        // Only include return payments if filter is 'all' or 'received_for_purchase_return'
        $returnPayments = collect();
        if ($filter === 'all' || $filter === 'received_for_purchase_return') {
            $returnPayments = $returnPaymentsQuery->get()->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'invoice_no' => $payment->purchaseReturn->return_invoice_no ?? 'N/A',
                    'date' => $payment->paid_at ?? $payment->created_at,
                    'supplier' => $payment->purchaseReturn->purchase->supplier,
                    'trx' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                    'reason' => 'Received For Purchase Return',
                    'amount' => (float) $payment->amount,
                    'type' => 'receipt',
                    'user' => $payment->user
                ];
            });
        }

        // Merge and sort
        $allPayments = $purchasePayments->merge($returnPayments)
            ->sortByDesc('date')
            ->values();

        return view('supplier-payments.index', [
            'payments' => $allPayments,
            'search' => $query,
            'filter' => $filter,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function exportPdf(Request $request)
    {
        $query = $request->get('search', '');
        $filter = $request->get('filter', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $payments = collect();

        // Similar logic as allPayments but for PDF
        $purchasePaymentsQuery = PurchasePayment::with('purchase.supplier')
            ->when($query, function ($q, $search) {
                $q->whereHas('purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchase', function ($pq) use ($search) {
                    $pq->where('invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            });

        if ($filter === 'paid_for_purchase') {
            $purchasePaymentsQuery->whereHas('purchase');
        }

        $purchasePayments = $purchasePaymentsQuery->get()->map(function ($payment) {
            return [
                'invoice_no' => $payment->purchase->invoice_no,
                'date' => $payment->created_at->format('Y-m-d'),
                'supplier' => $payment->purchase->supplier->name,
                'trx' => 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'reason' => 'Paid For Purchase',
                'amount' => (float) $payment->amount,
                'type' => 'payment'
            ];
        });

        $returnPaymentsQuery = PurchaseReturnPayment::with('purchaseReturn.purchase.supplier')
            ->when($query, function ($q, $search) {
                $q->whereHas('purchaseReturn.purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchaseReturn', function ($pq) use ($search) {
                    $pq->where('return_invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('paid_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('paid_at', '<=', $date);
            });

        if ($filter === 'received_for_purchase_return') {
            $returnPaymentsQuery->whereHas('purchaseReturn');
        }

        $returnPayments = $returnPaymentsQuery->get()->map(function ($payment) {
            return [
                'invoice_no' => $payment->purchaseReturn->return_invoice_no,
                'date' => ($payment->paid_at ?? $payment->created_at)->format('Y-m-d'),
                'supplier' => $payment->purchaseReturn->purchase->supplier->name,
                'trx' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'reason' => 'Received For Purchase Return',
                'amount' => (float) $payment->amount,
                'type' => 'receipt'
            ];
        });

        $allPayments = $purchasePayments->merge($returnPayments)
            ->sortByDesc('date')
            ->values();

        $pdf = Pdf::loadView('supplier-payments.pdf', [
            'payments' => $allPayments,
            'filter' => $filter,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        return $pdf->download('supplier-payments.pdf');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = $request->get('search', '');
        $filter = $request->get('filter', 'all');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $payments = collect();

        // Similar logic as allPayments but for CSV
        $purchasePaymentsQuery = PurchasePayment::with('purchase.supplier')
            ->when($query, function ($q, $search) {
                $q->whereHas('purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchase', function ($pq) use ($search) {
                    $pq->where('invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('created_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('created_at', '<=', $date);
            });

        if ($filter === 'paid_for_purchase') {
            $purchasePaymentsQuery->whereHas('purchase');
        }

        $purchasePayments = $purchasePaymentsQuery->get()->map(function ($payment) {
            return [
                'invoice_no' => $payment->purchase->invoice_no,
                'date' => $payment->created_at->format('Y-m-d'),
                'supplier' => $payment->purchase->supplier->name,
                'trx' => 'PAY-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'reason' => 'Paid For Purchase',
                'amount' => (float) $payment->amount,
                'type' => 'payment'
            ];
        });

        $returnPaymentsQuery = PurchaseReturnPayment::with('purchaseReturn.purchase.supplier')
            ->when($query, function ($q, $search) {
                $q->whereHas('purchaseReturn.purchase.supplier', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                      ->orWhere('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('purchaseReturn', function ($pq) use ($search) {
                    $pq->where('return_invoice_no', 'like', "%{$search}%");
                });
            })
            ->when($startDate, function ($q, $date) {
                $q->whereDate('paid_at', '>=', $date);
            })
            ->when($endDate, function ($q, $date) {
                $q->whereDate('paid_at', '<=', $date);
            });

        if ($filter === 'received_for_purchase_return') {
            $returnPaymentsQuery->whereHas('purchaseReturn');
        }

        $returnPayments = $returnPaymentsQuery->get()->map(function ($payment) {
            return [
                'invoice_no' => $payment->purchaseReturn->return_invoice_no,
                'date' => ($payment->paid_at ?? $payment->created_at)->format('Y-m-d'),
                'supplier' => $payment->purchaseReturn->purchase->supplier->name,
                'trx' => 'REC-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'reason' => 'Received For Purchase Return',
                'amount' => (float) $payment->amount,
                'type' => 'receipt'
            ];
        });

        $allPayments = $purchasePayments->merge($returnPayments)
            ->sortByDesc('date')
            ->values();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="supplier-payments.csv"',
        ];

        $callback = function () use ($allPayments) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, ['S.N.', 'Invoice No.', 'Date', 'Supplier', 'TRX', 'Reason', 'Amount']);
            
            // CSV Data
            foreach ($allPayments as $index => $payment) {
                fputcsv($file, [
                    $index + 1,
                    $payment['invoice_no'],
                    $payment['date'],
                    $payment['supplier'],
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

