@php
    $kpis = $kpis ?? [];
    $compact = (bool) ($compact ?? false);
    $kpiThemes = [
        'operation_completion' => 'production',
        'operators' => 'production',
        'quality' => 'qc',
        'dispatch_score' => 'dispatch',
        'materials' => 'materials',
        'wastage' => 'dispatch',
        'session_waste' => 'slate',
        'serial_loss' => 'slate',
    ];
    $allKeys = array_keys($kpiThemes);
    $displayKpis = collect($kpis)
        ->filter(fn ($kpi) => in_array($kpi['key'] ?? '', $allKeys, true))
        ->when($compact, fn ($c) => $c->reject(fn ($kpi) => ($kpi['key'] ?? '') === 'materials'))
        ->take($compact ? 5 : 6)
        ->values();
@endphp

@if ($displayKpis->isNotEmpty())
    @if ($compact)
        @foreach ($displayKpis as $kpi)
            @php
                $theme = $kpiThemes[$kpi['key'] ?? ''] ?? 'slate';
                $primaryMetric = $kpi['metrics'][0] ?? null;
                $displayValue = $primaryMetric['value'] ?? ($kpi['placeholder'] ?? '—');
                $shortLabel = match ($kpi['key'] ?? '') {
                    'operation_completion' => __('Complete'),
                    'operators' => __('Ops'),
                    'quality' => __('QC'),
                    'dispatch_score' => __('Dispatch'),
                    'materials' => __('Mat'),
                    'wastage' => __('Waste'),
                    'session_waste' => __('Sess'),
                    'serial_loss' => __('Serial'),
                    default => \Illuminate\Support\Str::limit($kpi['label'] ?? '', 8, ''),
                };
            @endphp
            <div @class(['mes-kpi', 'mes-kpi--'.$theme, 'mes-kpi--warning' => ! empty($kpi['warning'])]) title="{{ $kpi['label'] ?? '' }}">
                <span class="mes-kpi__label">{{ $shortLabel }}</span>
                <span class="mes-kpi__value">{{ $displayValue }}</span>
            </div>
        @endforeach
    @else
        <section aria-label="{{ __('Performance metrics') }}">
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($displayKpis as $kpi)
                    @php
                        $theme = $kpiThemes[$kpi['key'] ?? ''] ?? 'slate';
                        $primaryMetric = $kpi['metrics'][0] ?? null;
                        $displayValue = $primaryMetric['value'] ?? ($kpi['placeholder'] ?? '—');
                    @endphp
                    <x-admin.job-kpi-tile
                        :theme="$theme"
                        :label="$kpi['label'] ?? ''"
                        :value="$displayValue"
                        :emphasis="! empty($kpi['warning'])"
                    />
                @endforeach
            </div>
        </section>
    @endif
@endif
