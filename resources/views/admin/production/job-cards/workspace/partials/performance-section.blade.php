@php
    $kpis = $kpis ?? [];
    $criticalKeys = ['quality', 'dispatch_score', 'operation_completion'];
    $advancedKeys = ['operators', 'materials', 'wastage', 'session_waste', 'serial_loss'];

    $critical = collect($kpis)->filter(fn ($kpi) => in_array($kpi['key'] ?? '', $criticalKeys, true))->values();
    $advanced = collect($kpis)->filter(fn ($kpi) => in_array($kpi['key'] ?? '', $advancedKeys, true))->values();
@endphp

@if ($critical->isNotEmpty() || $advanced->isNotEmpty())
    <section class="job-360-performance mb-4" aria-label="{{ __('Performance metrics') }}">
        @if ($critical->isNotEmpty())
            <div class="job-360-performance__critical">
                @foreach ($critical as $kpi)
                    <article @class([
                        'job-360-kpi',
                        'job-360-kpi--warning' => ! empty($kpi['warning']),
                        'job-360-kpi--'.($kpi['key'] ?? 'default'),
                    ])>
                        <div class="job-360-kpi__head">
                            @if (! empty($kpi['icon']))
                                <x-admin.icon :name="$kpi['icon']" class="h-4 w-4" />
                            @endif
                            <h3 class="job-360-kpi__title">{{ $kpi['label'] }}</h3>
                        </div>
                        @if (! empty($kpi['metrics']))
                            <dl class="job-360-kpi__metrics">
                                @foreach ($kpi['metrics'] as $metric)
                                    <div>
                                        <dt>{{ $metric['label'] }}</dt>
                                        <dd class="tabular-nums">{{ $metric['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                        @if (! empty($kpi['warning']))
                            <p class="job-360-kpi__warning">{{ $kpi['warning'] }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        @if ($advanced->isNotEmpty())
            <details class="job-360-performance__advanced">
                <summary>
                    <x-admin.icon name="chart-pie" class="h-4 w-4" />
                    {{ __('Performance details') }}
                    <span class="job-360-performance__count">{{ $advanced->count() }}</span>
                </summary>
                <div class="job-360-performance__advanced-grid">
                    @foreach ($advanced as $kpi)
                        <article class="job-360-kpi job-360-kpi--compact">
                            <div class="job-360-kpi__head">
                                @if (! empty($kpi['icon']))
                                    <x-admin.icon :name="$kpi['icon']" class="h-4 w-4" />
                                @endif
                                <h3 class="job-360-kpi__title">{{ $kpi['label'] }}</h3>
                            </div>
                            @if (! empty($kpi['metrics']))
                                <dl class="job-360-kpi__metrics">
                                    @foreach ($kpi['metrics'] as $metric)
                                        <div>
                                            <dt>{{ $metric['label'] }}</dt>
                                            <dd class="tabular-nums">{{ $metric['value'] }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            @elseif (! empty($kpi['placeholder']))
                                <p class="job-360-kpi__placeholder">{{ $kpi['placeholder'] }}</p>
                            @endif
                            @if (! empty($kpi['warning']))
                                <p class="job-360-kpi__warning">{{ $kpi['warning'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </details>
        @endif
    </section>
@endif
