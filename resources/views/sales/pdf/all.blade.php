<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Sales</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header { width: 100%; margin-bottom: 16px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .title { font-size: 18px; font-weight: bold; color: #0a1233; }
        .brand { text-align: right; vertical-align: top; }
        .brand img { max-height: 40px; max-width: 140px; }
        .brand-name { font-size: 14px; font-weight: bold; color: #0a1233; margin-top: 4px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th { background: #0a1233; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.data td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="title">All Sales</td>
            <td class="brand">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo">
                @endif
                <div class="brand-name">{{ $generalSetting->site_title ?? 'MGEDC' }}</div>
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th style="width:28px;">S.N.</th>
                <th>Invoice</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Warehouse</th>
                <th class="num">Receivable</th>
                <th class="num">Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $i => $s)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $s->invoice_no }}</td>
                    <td>{{ $s->sale_date->format('m/d/Y') }}</td>
                    <td>{{ $s->customer->name }}</td>
                    <td>{{ $s->warehouse->name }}</td>
                    <td class="num">{{ formatCurrency($s->receivable_amount) }}</td>
                    <td class="num">{{ formatCurrency($s->due_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
