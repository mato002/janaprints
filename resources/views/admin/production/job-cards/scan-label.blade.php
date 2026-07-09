<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $label['job_number'] }} — {{ __('Scan label') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 24px; }
        .label { border: 2px solid #111; padding: 20px; width: 320px; margin: 0 auto; text-align: center; }
        .customer { font-size: 13px; color: #334155; margin-bottom: 8px; }
        .code { font-size: 26px; font-weight: 700; letter-spacing: 3px; margin: 10px 0; font-family: ui-monospace, monospace; }
        .meta { font-size: 11px; color: #64748b; margin-top: 8px; }
        .qr { margin: 12px auto; max-width: 160px; }
        .qr svg { width: 100%; height: auto; }
    </style>
</head>
<body onload="window.print()">
    <div class="label">
        <div class="customer">{{ $label['customer'] ?? '—' }}</div>
        <div class="code">{{ $label['barcode'] }}</div>
        <div class="qr">{!! $label['qr_svg'] !!}</div>
        <div class="meta">
            @if ($label['sales_order'])
                {{ __('Order') }}: {{ $label['sales_order'] }} ·
            @endif
            @if ($label['department_label'])
                {{ $label['department_label'] }}
            @endif
        </div>
        <div class="meta">{{ $label['scan_url'] }}</div>
    </div>
</body>
</html>
