<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header { width: 100%; margin-bottom: 16px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .title { font-size: 18px; font-weight: bold; color: #0a1233; }
        .brand { text-align: right; vertical-align: top; }
        .brand img { max-height: 40px; max-width: 140px; }
        .brand-name { font-size: 14px; font-weight: bold; color: #0a1233; margin-top: 4px; }
        .report-meta { font-size: 10px; color: #6b7280; margin-top: 8px; line-height: 1.4; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.data th { background: #0a1233; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.data td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        .num { text-align: right; }
        .footer { margin-top: 20px; font-size: 10px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <!-- Header with Logo and Company Info -->
    <table class="header-table">
        <tr>
            <td class="title">
                @if($filterBy === 'warehouse' && $warehouse)
                    Stock Report - {{ $warehouse->name }}
                @elseif($filterBy === 'product' && $product)
                    Stock Report - {{ $product->name }}
                @else
                    Stock Report
                @endif
            </td>
            <td class="brand">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo">
                @endif
                <div class="brand-name">{{ $generalSetting?->site_title ?? $generalSetting?->company_name ?? 'MGEDC System' }}</div>
            </td>
        </tr>
    </table>

    <!-- Report Metadata -->
    <div class="report-meta" style="display: none;"></div>

    <!-- Data Table -->
    <table class="data">
        <thead>
            <tr>
                @if($filterBy === 'warehouse')
                    <th style="width:28px;">S.N.</th>
                    <th>Product Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th class="num">Stock</th>
                @else
                    <th style="width:28px;">S.N.</th>
                    <th>Warehouse</th>
                    <th class="num">Current Stock</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($stockData as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @if($filterBy === 'warehouse')
                        <td>{{ $item['product_name'] }}</td>
                        <td>{{ $item['sku'] }}</td>
                        <td>
                            @php
                                $product = \App\Models\Product::find($item['product_id']);
                            @endphp
                            {{ $product?->category?->name ?? '-' }}
                        </td>
                        <td>{{ $product?->brand?->name ?? '-' }}</td>
                        <td class="num">{{ number_format($item['stock_quantity'], 0) }} {{ $item['unit'] }}</td>
                    @else
                        <td>{{ $item['warehouse_name'] }}</td>
                        <td class="num">{{ number_format($item['stock_quantity'], 0) }} {{ $item['unit'] }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $filterBy === 'warehouse' ? '6' : '3' }}" style="text-align: center; color: #9ca3af;">
                        No data available
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>{{ $generalSetting?->company_name ?? 'MGEDC System' }} - Stock Report</p>
    </div>
</body>
</html>
