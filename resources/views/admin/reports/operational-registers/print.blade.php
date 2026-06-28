<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} — {{ config('app.name') }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        p { margin: 0 0 16px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; font-size: 11px; text-transform: uppercase; }
        .summary { display: flex; gap: 16px; margin-bottom: 16px; }
        .summary div { border: 1px solid #ddd; padding: 8px 12px; }
    </style>
</head>
<body onload="window.print()">
    <h1>{{ $active_register_label }}</h1>
    <p>{{ $period_label }} · {{ $branch_label }}</p>

    @if (! empty($tab_data['summary']))
        <div class="summary">
            @foreach ($tab_data['summary'] as $item)
                <div><strong>{{ $item['label'] }}</strong><br>{{ $item['value'] }}</div>
            @endforeach
        </div>
    @endif

    @include('admin.reports.operational-registers.partials.register-table', [
        'table' => $tab_data['table'] ?? [],
        'print' => true,
    ])
</body>
</html>
