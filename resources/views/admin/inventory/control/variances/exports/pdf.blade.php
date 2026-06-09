<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Inventory Variance Report') }}</title>
    <style>
        body { font-family: Inter, Arial, sans-serif; font-size: 12px; color: #1e293b; padding: 24px; }
        h1, h2 { margin: 0 0 8px; }
        .meta { margin-bottom: 20px; }
        .meta dt { color: #64748b; font-size: 11px; }
        .meta dd { margin: 0 0 8px; font-weight: 600; }
        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 16px 0 24px; }
        .card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .card .label { color: #64748b; font-size: 11px; }
        .card .value { font-size: 16px; font-weight: 700; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f8fafc; }
        .totals { margin-top: 20px; width: 50%; }
        .muted { color: #64748b; font-size: 11px; }
    </style>
</head>
<body>
    <h1>{{ __('Inventory Variance Report') }}</h1>
    <p class="muted">{{ __('Generated') }}: {{ $generatedAt->format('Y-m-d H:i') }}</p>

    <dl class="meta grid">
        <div><dt>{{ __('Warehouse') }}</dt><dd>{{ $meta['warehouse'] }}</dd></div>
        <div><dt>{{ __('Count date') }}</dt><dd>{{ $meta['count_date'] }}</dd></div>
        <div><dt>{{ __('Prepared by') }}</dt><dd>{{ $meta['prepared_by'] ?? '—' }}</dd></div>
        <div><dt>{{ __('Approved by') }}</dt><dd>{{ $meta['approved_by'] ?? '—' }}</dd></div>
    </dl>

    <h2>{{ __('Variance summary') }}</h2>
    <div class="grid">
        <div class="card"><div class="label">{{ __('Expected qty') }}</div><div class="value">{{ number_format($summary['expected_qty'], 3) }}</div></div>
        <div class="card"><div class="label">{{ __('Counted qty') }}</div><div class="value">{{ number_format($summary['counted_qty'], 3) }}</div></div>
        <div class="card"><div class="label">{{ __('Variance qty') }}</div><div class="value">{{ number_format($summary['variance_qty'], 3) }}</div></div>
        <div class="card"><div class="label">{{ __('Variance cost') }}</div><div class="value">{{ number_format($summary['variance_cost'], 2) }}</div></div>
    </div>

    <h2>{{ __('Detailed variances') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('Item') }}</th>
                <th>{{ __('SKU') }}</th>
                <th>{{ __('Expected') }}</th>
                <th>{{ __('Counted') }}</th>
                <th>{{ __('Variance') }}</th>
                <th>{{ __('Unit cost') }}</th>
                <th>{{ __('Variance value') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($lines as $line)
                <tr>
                    <td>{{ $line->inventoryItem?->item_name }}</td>
                    <td>{{ $line->inventoryItem?->sku }}</td>
                    <td>{{ $line->system_quantity }}</td>
                    <td>{{ $line->counted_quantity }}</td>
                    <td>{{ $line->variance_quantity }}</td>
                    <td>{{ number_format((float) $line->system_unit_cost, 2) }}</td>
                    <td>{{ number_format((float) $line->variance_value, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7">{{ __('No variance lines found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>{{ __('Totals') }}</h2>
    <table class="totals">
        <tbody>
            <tr><th>{{ __('Positive variance') }}</th><td>{{ number_format($totals['positive_variance'], 2) }}</td></tr>
            <tr><th>{{ __('Negative variance') }}</th><td>{{ number_format($totals['negative_variance'], 2) }}</td></tr>
            <tr><th>{{ __('Net variance') }}</th><td>{{ number_format($totals['net_variance'], 2) }}</td></tr>
        </tbody>
    </table>
</body>
</html>
