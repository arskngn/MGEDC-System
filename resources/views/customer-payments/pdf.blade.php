<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Payments Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #5542ff;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #0a1233;
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            color: #666;
            margin: 5px 0 0;
            font-size: 14px;
        }
        .filters {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9ff;
            border-radius: 5px;
            border-left: 4px solid #5542ff;
        }
        .filters p {
            margin: 5px 0;
            font-size: 13px;
            color: #555;
        }
        .filters strong {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: #5542ff;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-left {
            text-align: left;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-blue {
            color: #2563eb;
        }
        .text-green {
            color: #059669;
        }
        .text-red {
            color: #dc2626;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 500;
        }
        .badge-receipt {
            background: #d1fae5;
            color: #059669;
        }
        .badge-payment {
            background: #fee2e2;
            color: #dc2626;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Customer Payments Report</h1>
            <p>Generated on {{ date('Y-m-d H:i:s') }}</p>
        </div>

        @if($search || $filter !== 'all' || $startDate || $endDate)
            <div class="filters">
                <p><strong>Filters Applied:</strong></p>
                @if($search)
                    <p>Search: {{ $search }}</p>
                @endif
                @if($filter !== 'all')
                    <p>Filter: {{ $filter === 'received_from_customer' ? 'Received From Customer' : 'Paid For Sale Return' }}</p>
                @endif
                @if($startDate && $endDate)
                    <p>Date Range: {{ $startDate }} to {{ $endDate }}</p>
                @elseif($startDate)
                    <p>From: {{ $startDate }}</p>
                @elseif($endDate)
                    <p>To: {{ $endDate }}</p>
                @endif
            </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>S.N.</th>
                    <th>Invoice No.</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>TRX</th>
                    <th>Reason</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $i => $payment)
                    @php
                        $isReceipt = $payment['type'] === 'receipt';
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="font-bold text-blue text-left">{{ $payment['invoice_no'] }}</td>
                        <td>{{ $payment['date']->format('Y-m-d') }}</td>
                        <td class="text-left">{{ $payment['customer']->name ?? 'N/A' }}</td>
                        <td class="font-mono">{{ $payment['trx'] }}</td>
                        <td>
                            <span class="badge {{ $isReceipt ? 'badge-receipt' : 'badge-payment' }}">
                                {{ $payment['reason'] }}
                            </span>
                        </td>
                        <td class="font-bold {{ $isReceipt ? 'text-green' : 'text-red' }}">
                            {{ formatCurrency($payment['amount']) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="no-data">No customer payments found matching the current criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($payments->isNotEmpty())
            <div class="footer">
                <p>Total Records: {{ $payments->count() }}</p>
                <p>Report generated by {{ auth()->user()->name ?? 'System' }}</p>
            </div>
        @endif
    </div>
</body>
</html>
