<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Receipt :number', ['number' => $receipt['receipt_number']]) }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #ddd; padding: 6px 4px; text-align: left; }
        .right { text-align: right; }
        .totals { margin-top: 16px; }
        .totals div { display: flex; justify-content: space-between; margin: 4px 0; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    @include('admin.sales.payments.partials.receipt-content')
</body>
</html>
