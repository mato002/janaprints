<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} — {{ now()->format('Y-m-d') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; font-size: 12px; color: #1e293b; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { color: #64748b; margin-top: 0; }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 16px 0; }
        .metric { border: 1px solid #e2e8f0; padding: 10px; border-radius: 6px; }
        .metric-label { font-size: 10px; text-transform: uppercase; color: #64748b; }
        .metric-value { font-size: 16px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        th { background: #f8fafc; font-size: 10px; text-transform: uppercase; }
        h3 { font-size: 13px; margin: 20px 0 8px; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
    <p>{{ __('Period') }}: {{ $filters['from_date'] }} — {{ $filters['to_date'] }}</p>

    @include('admin.reports.hr.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
        'print_mode' => true,
    ])
</body>
</html>
