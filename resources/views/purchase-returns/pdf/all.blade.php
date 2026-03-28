<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Purchase Return</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header-table { width: 100%; border-collapse: collapse; }
        .title { font-size: 18px; font-weight: bold; color: #0a1233; font-family: DejaVu Serif, serif; }
        .brand { text-align: right; vertical-align: top; }
        .brand img { max-height: 40px; max-width: 140px; }
        .brand-name { font-size: 14px; font-weight: bold; color: #0a1233; margin-top: 4px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th { background: #2563eb; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; font-family: DejaVu Serif, serif; }
        table.data td { padding: 6px; border-bottom: 1px solid #e5e7eb; font-family: DejaVu Serif, serif; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="title">All Purchase Return</td>
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
                <th>Supplier</th>
                <th>Warehouse</th>
                <th class="num">Receivable</th>
                <th class="num">Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returns as $i => $r)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->return_invoice_no }}</td>
                    <td>{{ $r->return_date->format('m/d/Y') }}</td>
                    <td>{{ $r->purchase->supplier->name }}</td>
                    <td>{{ $r->warehouse->name }}</td>
                    <td class="num">{{ formatCurrency($r->receivable_amount) }}</td>
                    <td class="num">{{ formatCurrency($r->due_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 28px; font-size: 9px; color: #9ca3af;">Powered by {{ $generalSetting->site_title ?? 'MGEDC' }}</div>
</body>
</html>
