<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Access Audit Report') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #1e293b; margin: 24px; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #64748b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f8fafc; font-size: 11px; text-transform: uppercase; }
        .risk-critical { color: #b91c1c; font-weight: bold; }
        .risk-high { color: #c2410c; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ __('Access Audit Report') }}</h1>
    <p class="meta">{{ __('Generated') }}: {{ $generatedAt->format('M j, Y g:i A') }} · {{ __('Records') }}: {{ $events->count() }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('Timestamp') }}</th>
                <th>{{ __('User') }}</th>
                <th>{{ __('Module') }}</th>
                <th>{{ __('Action') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('IP') }}</th>
                <th>{{ __('Risk') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($events as $event)
                <tr>
                    <td>{{ $event->occurred_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $event->user?->name ?? '—' }}</td>
                    <td>{{ \Illuminate\Support\Str::headline($event->module) }}</td>
                    <td>{{ \Illuminate\Support\Str::headline($event->action) }}</td>
                    <td>{{ $event->description }}</td>
                    <td>{{ $event->ip_address ?? '—' }}</td>
                    <td class="risk-{{ $event->risk_level->value }}">{{ $event->risk_level->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
