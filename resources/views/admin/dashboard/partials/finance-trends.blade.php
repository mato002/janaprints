@php
    $panels = [
        ['key' => 'revenue', 'title' => __('Revenue Trend'), 'meta' => __('Ledger MTD months'), 'class' => ''],
        ['key' => 'expenses', 'title' => __('Expense Trend'), 'meta' => __('Ledger MTD months'), 'class' => 'exec-bar-chart__bar--production'],
        ['key' => 'cash_flow', 'title' => __('Cash Flow Trend'), 'meta' => __('Cash accounts'), 'class' => 'exec-bar-chart__bar--collections'],
    ];
@endphp
<section class="exec-charts-grid exec-charts-grid--finance" aria-label="{{ __('Finance trends') }}">
    @foreach ($panels as $panel)
        @php
            $chart = $trends[$panel['key']] ?? [];
            $hasData = collect($chart)->sum('value') !== 0.0;
        @endphp
        <div class="exec-chart-panel">
            <div class="exec-chart-panel__head">
                <h3 class="exec-chart-panel__title">{{ $panel['title'] }}</h3>
                <span class="exec-chart-panel__meta">{{ $panel['meta'] }}</span>
            </div>
            @if ($hasData)
                <div class="exec-bar-chart exec-bar-chart--tall" role="img" aria-label="{{ $panel['title'] }}">
                    @foreach ($chart as $point)
                        <div class="exec-bar-chart__col" title="{{ $point['label'] }}: {{ number_format($point['value'], 0) }}">
                            <div class="exec-bar-chart__bar {{ $panel['class'] }}" style="height: {{ max($point['percent'] ?? 4, 4) }}%"></div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-admin.exec-empty-state
                    :title="__('No ledger activity yet')"
                    :description="__('Trend bars appear when journals are posted.')"
                    compact
                />
            @endif
        </div>
    @endforeach
</section>
