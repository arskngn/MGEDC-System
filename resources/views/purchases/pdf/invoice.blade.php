<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase {{ $purchase->invoice_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .brand { text-align: right; vertical-align: top; }
        .brand img { max-height: 48px; max-width: 160px; }
        .brand-name { font-size: 15px; font-weight: bold; color: #0a1233; margin-top: 4px; }
        .company { font-size: 11px; margin-top: 6px; color: #374151; }
        h1 { font-size: 20px; color: #0a1233; margin: 0 0 12px 0; font-family: DejaVu Serif, serif; }
        .meta { font-size: 11px; margin-bottom: 4px; }
        .meta strong { color: #111; }
        .supplier-block { margin: 16px 0; padding: 12px; background: #f9fafb; border-radius: 4px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.items th { background: #2563eb; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; }
        table.items td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
        .num { text-align: right; }
        .summary { width: 280px; margin-left: auto; margin-top: 16px; font-size: 11px; }
        .summary table { width: 100%; border-collapse: collapse; }
        .summary td { padding: 4px 0; }
        .summary td:first-child { color: #6b7280; }
        .summary td:last-child { text-align: right; font-weight: bold; }
        .footer { text-align: center; margin-top: 28px; font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <h1>Purchase Details</h1>
            </td>
            <td class="brand">
                @if(!empty($logoSrc))
                    <img src="{{ $logoSrc }}" alt="Logo">
                @endif
                <div class="brand-name">{{ $generalSetting->site_title ?? 'MGEDC' }}</div>
                <div class="company">Company: {{ $generalSetting->full_company_name ?? 'Mindoro Golden Eagle Distribution Corporation' }}</div>
                <div class="meta" style="margin-top:8px;"><strong>Invoice No.:</strong> #{{ $purchase->invoice_no }}</div>
                <div class="meta"><strong>Date:</strong> {{ $purchase->purchase_date->format('d F Y') }}</div>
                <div class="meta"><strong>Warehouse:</strong> {{ $purchase->warehouse->name }}</div>
            </td>
        </tr>
    </table>

    <div class="supplier-block">
        <div><strong>Name:</strong> {{ $purchase->supplier->name }}</div>
        <div><strong>Mobile:</strong> {{ $purchase->supplier->phone ?? '—' }}</div>
        <div><strong>Email:</strong> {{ $purchase->supplier->email ?? '—' }}</div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:36px;">S.N.</th>
                <th>Name</th>
                <th>SKU</th>
                <th>Quantity</th>
                <th class="num">Unit Price</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->items as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->product_name }}</td>
                    <td>{{ $line->sku }}</td>
                    <td>{{ rtrim(rtrim(number_format((float) $line->quantity, 4, '.', ''), '0'), '.') }} {{ $line->unit_label }}</td>
                    <td class="num">{{ formatCurrency($line->unit_price) }}</td>
                    <td class="num">{{ formatCurrency($line->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <td>Subtotal</td>
                <td>{{ formatCurrency($purchase->subtotal) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td>{{ formatCurrency($purchase->discount) }}</td>
            </tr>
            <tr>
                <td>Grand Total</td>
                <td>{{ formatCurrency($purchase->payable_amount) }}</td>
            </tr>
            <tr>
                <td>Paid</td>
                <td>{{ formatCurrency($purchase->paid_amount) }}</td>
            </tr>
            <tr>
                <td>Payable (Balance)</td>
                <td>{{ formatCurrency($purchase->due_amount) }}</td>
            </tr>
        </table>
    </div>

    @if($purchase->note)
        <p style="margin-top:20px;font-size:10px;color:#4b5563;"><strong>Note:</strong> {{ $purchase->note }}</p>
    @endif

    <div class="footer">Powered by {{ $generalSetting->site_title ?? 'MGEDC' }}</div>
</body>
</html>
