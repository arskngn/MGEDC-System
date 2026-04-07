<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Payments Report</title>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
            color: #111; 
            margin: 0;
            padding: 0;
        }
        .header { 
            width: 100%; 
            margin-bottom: 20px; 
        }
        .header-table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .title { 
            font-size: 18px; 
            font-weight: bold; 
            color: #0a1233;
            vertical-align: top;
        }
        .brand { 
            text-align: right; 
            vertical-align: top; 
        }
        .brand img { 
            max-height: 50px; 
            max-width: 150px;
            margin-bottom: 4px;
        }
        .brand-name { 
            font-size: 14px; 
            font-weight: bold; 
            color: #0a1233; 
            margin: 4px 0 0 0;
        }
        .company { 
            font-size: 10px; 
            color: #666; 
            margin: 2px 0;
        }
        table.data { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 16px;
        }
        table.data th { 
            background: #2563eb; 
            color: #fff; 
            padding: 10px 6px; 
            text-align: center; 
            font-size: 10px; 
            text-transform: uppercase;
            font-weight: bold;
        }
        table.data td { 
            padding: 8px 6px; 
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }
        table.data tr:nth-child(even) td { 
            background: #f8fafc; 
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .badge { 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-size: 9px; 
            font-weight: bold;
            white-space: nowrap;
        }
        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-green {
            background: #dcfce7;
            color: #166534;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 9px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="title">Supplier Payments</td>
            <td class="brand">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo">
                @endif
                <div class="brand-name">{{ $generalSetting->site_title ?? 'MGEDC' }}</div>
                <div class="company">{{ $generalSetting->full_company_name ?? 'Mindoro Golden Eagle Distribution Corporation' }}</div>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 28px;">S.N.</th>
                <th>Invoice</th>
                <th>Date</th>
                <th style="width: 100px;">Supplier</th>
                <th>Trx</th>
                <th style="width: 80px;">Reason</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $i => $payment)
                @php
                    $isPayment = $payment['type'] === 'payment';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="text-left">{{ $payment['invoice_no'] }}</td>
                    <td>{{ $payment['date'] }}</td>
                    <td class="text-left">{{ $payment['supplier'] }}</td>
                    <td class="text-left">{{ $payment['trx'] }}</td>
                    <td>
                        <span class="badge {{ $isPayment ? 'badge-red' : 'badge-green' }}">
                            {{ $payment['reason'] }}
                        </span>
                    </td>
                    <td class="text-right" style="color: {{ $isPayment ? '#dc2626' : '#16a34a' }}; font-weight: bold;">
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
