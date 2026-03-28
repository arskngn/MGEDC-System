<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Payments Report</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #5542ff;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #0a1233;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .filters {
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .filters span {
            margin-right: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #5542ff;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 12px 8px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 10px 8px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        .text-red {
            color: #dc3545;
            font-weight: bold;
        }
        .text-green {
            color: #28a745;
            font-weight: bold;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-red {
            background: #f8d7da;
            color: #721c24;
        }
        .badge-green {
            background: #d4edda;
            color: #155724;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Supplier Payments Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    @if($filter !== 'all' || $startDate || $endDate)
        <div class="filters">
            <strong>Filters Applied:</strong>
            @if($filter !== 'all')
                <span>Type: {{ $filter === 'paid_for_purchase' ? 'Paid For Purchase' : 'Received For Purchase Return' }}</span>
            @endif
            @if($startDate)
                <span>From: {{ $startDate }}</span>
            @endif
            @if($endDate)
                <span>To: {{ $endDate }}</span>
            @endif
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>S.N.</th>
                <th>Invoice No.</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>TRX</th>
                <th>Reason</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $i => $payment)
                @php
                    $isPayment = $payment['type'] === 'payment';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $payment['invoice_no'] }}</td>
                    <td>{{ $payment['date'] }}</td>
                    <td>{{ $payment['supplier'] }}</td>
                    <td>{{ $payment['trx'] }}</td>
                    <td>
                        <span class="badge {{ $isPayment ? 'badge-red' : 'badge-green' }}">
                            {{ $payment['reason'] }}
                        </span>
                    </td>
                    <td class="{{ $isPayment ? 'text-red' : 'text-green' }}">
                        {{ formatCurrency($payment['amount']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 30px;">No supplier payments found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated from MGEDC Inventory Management System</p>
    </div>
</body>
</html>
