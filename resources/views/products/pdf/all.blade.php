<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Products</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header-table { width: 100%; border-collapse: collapse; }
        .title { font-size: 18px; font-weight: bold; color: #0a1233; font-family: DejaVu Serif, serif; }
        .brand { text-align: right; vertical-align: top; }
        .brand img { max-height: 40px; max-width: 140px; }
        .brand-name { font-size: 14px; font-weight: bold; color: #0a1233; margin-top: 4px; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th { background: #5542ff; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; font-family: DejaVu Serif, serif; }
        table.data td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="title">Products</td>
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
                <th>SKU</th>
                <th>Name</th>
                <th>Brand</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $i => $p)
                @php
                    $u = $p->unit->short_name ?? $p->unit->name;
                    $stock = rtrim(rtrim(number_format((float) $p->current_stock, 4, '.', ''), '0'), '.') ?: '0';
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $p->sku }}</td>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->brand?->name ?? '—' }}</td>
                    <td>{{ $stock }} {{ $u }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 28px; font-size: 9px; color: #9ca3af;">Powered by {{ $generalSetting->site_title ?? 'MGEDC' }}</div>
</body>
</html>
