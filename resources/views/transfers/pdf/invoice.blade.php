<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transfer {{ $transfer->tracking_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 0; padding: 20px; }
        .header { position: relative; margin-bottom: 24px; border-bottom: 1px solid #e5e7eb; padding-bottom: 16px; }
        .logo-section { position: absolute; top: 0; right: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        .logo-section img { max-height: 60px; max-width: 120px; }
        .logo-title { font-size: 16px; font-weight: bold; color: #0a1233; }
        .title-section { padding-right: 140px; }
        h1 { font-size: 18px; color: #0a1233; margin: 0 0 12px 0; }
        .meta { font-size: 11px; margin: 4px 0; line-height: 1.5; }
        .meta strong { color: #111; font-weight: bold; }
        .detail-block { margin: 16px 0; padding: 12px; background: #f9fafb; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.items th { background: #2563eb; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; font-weight: bold; }
        table.items td { padding: 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        .num { text-align: right; }
        .footer { text-align: center; margin-top: 24px; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-section">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" alt="Logo">
            @endif
            <div class="logo-title">{{ $generalSetting->site_title ?? 'MGEDC' }}</div>
        </div>
        <div class="title-section">
            <h1>Transfer Details</h1>
            <div class="meta"><strong>Tracking No.:</strong> {{ $transfer->tracking_no }}</div>
            <div class="meta"><strong>Date:</strong> {{ $transfer->transfer_date->format('d F Y') }}</div>
            <div class="meta"><strong>From:</strong> {{ $transfer->fromWarehouse->name }}</div>
            <div class="meta"><strong>To:</strong> {{ $transfer->toWarehouse->name }}</div>
        </div>
    </div>

    @if($transfer->note)
    <div class="detail-block">
        <div><strong>Note:</strong></div>
        <div style="margin-top: 4px;">{{ $transfer->note }}</div>
    </div>
    @endif

    <table class="items">
        <thead>
            <tr>
                <th style="width: 10%;">S.N.</th>
                <th style="width: 50%;">Product</th>
                <th style="width: 20%;" class="num">Quantity</th>
                <th style="width: 20%;">Unit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfer->items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="num">{{ $item->quantity }}</td>
                    <td>{{ $item->unit_label }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 12px;">No items found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Powered by {{ $generalSetting->site_title ?? 'MGEDC' }}</p>
    </div>
</body>
</html>
