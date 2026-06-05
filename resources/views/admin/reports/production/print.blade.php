<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — {{ $filters['from_date'] }} {{ __('to') }} {{ $filters['to_date'] }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; color: #0f172a; margin: 1.5rem; }
        h1 { font-size: 1.25rem; margin-bottom: 0.25rem; }
        p { color: #64748b; font-size: 0.875rem; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0 1.5rem; font-size: 0.8125rem; }
        th, td { border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; text-align: left; }
        th { background: #f8fafc; text-transform: uppercase; font-size: 0.6875rem; letter-spacing: 0.04em; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin: 1rem 0; }
        .metric { border: 1px solid #e2e8f0; padding: 0.75rem; }
        .metric-label { font-size: 0.6875rem; color: #64748b; text-transform: uppercase; }
        .metric-value { font-size: 1.125rem; font-weight: 700; margin-top: 0.25rem; }
        .section-title { font-size: 0.9375rem; font-weight: 600; margin: 1.25rem 0 0.5rem; }
        @media print {
            .no-print { display: none; }
            body { margin: 0.5rem; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 1rem;">
        <button type="button" onclick="window.print()" style="padding: 0.5rem 1rem; cursor: pointer;">
            {{ __('Print / Save as PDF') }}
        </button>
    </div>

    <h1>{{ $title }}</h1>
    <p>{{ $description }}</p>
    <p>{{ __('Period') }}: {{ $filters['from_date'] }} — {{ $filters['to_date'] }}</p>

    @include('admin.reports.production.partials.tab-content', [
        'tab_data' => $tab_data,
        'active_tab' => $active_tab,
        'print_mode' => true,
    ])
</body>
</html>
