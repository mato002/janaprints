@php $production = $tabData['production'] ?? []; $serial = $tabData['serial_summary'] ?? []; @endphp

<div class="space-y-4 text-sm">
    <section>
        <h3 class="mb-2 font-semibold text-slate-900">{{ __('Production notes') }}</h3>
        <p class="whitespace-pre-wrap text-slate-700">{{ $tabData['production_notes'] ?: '—' }}</p>
    </section>
    <section>
        <h3 class="mb-2 font-semibold text-slate-900">{{ __('Customer instructions') }}</h3>
        <p class="whitespace-pre-wrap text-slate-700">{{ $tabData['customer_instructions'] ?: '—' }}</p>
    </section>
    @if ($production !== [])
        <section>
            <h3 class="mb-2 font-semibold text-slate-900">{{ __('Route & BOM') }}</h3>
            <dl class="grid gap-2">
                <div><dt class="text-slate-500">{{ __('Route') }}</dt><dd>{{ $production['route_label'] ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('BOM') }}</dt><dd>{{ $production['bom_name'] ? $production['bom_name'].' v'.($production['bom_version'] ?? '') : '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('QC checklist') }}</dt><dd>{{ $production['qc_checklist'] ?? '—' }}</dd></div>
            </dl>
        </section>
    @endif
    @if ($serial['uses_serial_numbers'] ?? false)
        <section>
            <h3 class="mb-2 font-semibold text-slate-900">{{ __('Serial rule') }}</h3>
            <p><code>{{ $serial['resolved_prefix'] }}{{ $serial['next_number'] }}</code></p>
        </section>
    @endif
</div>
