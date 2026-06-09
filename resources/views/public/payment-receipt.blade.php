<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Receipt :number', ['number' => $receipt['receipt_number']]) }}</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f8fafc; margin: 0; padding: 24px; color: #0f172a; }
        .card { max-width: 520px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; font-size: 14px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 8px 4px; }
        .right { text-align: right; }
        .muted { color: #64748b; font-size: 13px; }
        .total { font-weight: 700; font-size: 16px; margin-top: 16px; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <div class="card">
        @include('admin.sales.payments.partials.receipt-content')
    </div>
</body>
</html>
