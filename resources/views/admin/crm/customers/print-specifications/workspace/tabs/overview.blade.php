@php
    $metrics = $workspace['usage_metrics'];
    $production = $tabData['production'] ?? [];
    $serial = $workspace['serial_summary'];
@endphp

<div class="grid gap-4 lg:grid-cols-2">
    <section>
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Usage intelligence') }}</h3>
        <dl class="grid grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500">{{ __('Orders') }}</dt><dd class="font-medium tabular-nums">{{ $metrics['orders_count'] }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Revenue') }}</dt><dd class="font-medium tabular-nums">{{ number_format((float) $metrics['total_revenue'], 2) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Last ordered') }}</dt><dd>{{ $metrics['last_ordered_at'] ? \Illuminate\Support\Carbon::parse($metrics['last_ordered_at'])->format('Y-m-d') : '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Last produced') }}</dt><dd>{{ $metrics['last_produced_at'] ? \Illuminate\Support\Carbon::parse($metrics['last_produced_at'])->format('Y-m-d') : '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Average quantity') }}</dt><dd class="tabular-nums">{{ $metrics['average_quantity'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Last selling price') }}</dt><dd class="tabular-nums">{{ $metrics['last_selling_price'] !== null ? number_format((float) $metrics['last_selling_price'], 2) : '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Current artwork') }}</dt><dd>{{ $workspace['header']['artwork_version'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Serial position') }}</dt><dd><code class="text-xs">{{ ($serial['uses_serial_numbers'] ?? false) ? (($serial['resolved_prefix'] ?? '').($serial['next_number'] ?? '')) : '—' }}</code></dd></div>
        </dl>
    </section>

    <section>
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Production intelligence') }}</h3>
        @if ($production === [])
            <p class="text-sm text-slate-500">{{ __('Link a product to view production defaults.') }}</p>
        @else
            <dl class="grid grid-cols-1 gap-2 text-sm">
                <div><dt class="text-slate-500">{{ __('Production route') }}</dt><dd>{{ $production['route_label'] ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('BOM version') }}</dt><dd>{{ $production['bom_name'] ? $production['bom_name'].' v'.($production['bom_version'] ?? '—') : '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('QC checklist') }}</dt><dd>{{ $production['qc_checklist'] ?? '—' }} @if(($production['qc_line_count'] ?? 0) > 0)({{ $production['qc_line_count'] }})@endif</dd></div>
                <div><dt class="text-slate-500">{{ __('Estimated duration') }}</dt><dd>{{ $production['estimated_duration_minutes'] ? $production['estimated_duration_minutes'].' '.__('min') : '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Estimated material cost') }}</dt><dd class="tabular-nums">{{ number_format((float) ($production['estimated_material_cost'] ?? 0), 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Estimated selling price') }}</dt><dd class="tabular-nums">{{ number_format((float) ($production['estimated_selling_price'] ?? 0), 2) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Serial rule') }}</dt><dd>{{ $production['serial_rule'] ?? '—' }}</dd></div>
            </dl>
        @endif
    </section>
</div>
