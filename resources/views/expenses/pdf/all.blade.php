<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Expenses</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 0; padding: 20px; }
        .header { display: flex; justify-content: flex-start; align-items: flex-start; margin-bottom: 20px; gap: 20px; position: relative; padding-right: 140px; min-height: 80px; }
        .logo-section { position: absolute; top: 0; right: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
        .logo-section img { max-height: 60px; max-width: 120px; }
        .logo-title { font-size: 16px; font-weight: bold; color: #0a1233; white-space: nowrap; }
        .title-section { flex: 1; }
        .title { font-size: 18px; font-weight: bold; color: #0a1233; margin: 0 0 4px 0; }
        .generated { font-size: 9px; color: #9ca3af; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table th { background: #2563eb; color: #fff; padding: 8px 6px; text-align: left; font-size: 10px; font-weight: bold; }
        table td { padding: 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        .footer { text-align: center; margin-top: 20px; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title-section">
            <div class="title">All Expenses</div>
            <div class="generated">Generated on {{ now()->format('d F Y H:i:s') }}</div>
        </div>
        <div class="logo-section">
            @if(!empty($logoSrc))
                <img src="{{ $logoSrc }}" alt="Logo">
            @endif
            <div class="logo-title">{{ $generalSetting->site_title ?? 'MGEDC' }}</div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">S.N.</th>
                <th style="width: 25%;">Type</th>
                <th style="width: 15%;">Date</th>
                <th style="width: 20%;">Amount</th>
                <th style="width: 35%;">Note</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $i => $expense)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $expense->expenseType->name }}</td>
                    <td>{{ $expense->date->format('d M, Y') }}</td>
                    <td>{{ $generalSetting?->currency_symbol ?? '$' }}{{ number_format($expense->amount, 2) }}</td>
                    <td>{{ Illuminate\Support\Str::limit($expense->description ?? '—', 40) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 12px;">No expenses found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Powered by {{ $generalSetting->site_title ?? 'MGEDC' }}</p>
    </div>
</body>
</html>
