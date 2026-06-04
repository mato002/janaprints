@php $sales = $dashboard['sales']; @endphp
<section class="exec-panel">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Sales Performance') }}</h2>
        <span class="text-[11px] text-slate-500">{{ __('30 days') }} · {{ $sales['revenue_trend'] }}</span>
    </div>
    <div class="exec-bar-chart" role="img" aria-label="{{ __('Sales last 30 days') }}">
        @foreach ($sales['chart'] as $point)
            <div class="exec-bar-chart__col" title="{{ $point['label'] }}: {{ number_format($point['value'], 0) }}">
                <div class="exec-bar-chart__bar" style="height: {{ max($point['percent'], 2) }}%"></div>
                @if ($loop->index % 5 === 0)
                    <span class="exec-bar-chart__label">{{ $point['label'] }}</span>
                @endif
            </div>
        @endforeach
    </div>
    <div class="exec-metric-grid exec-metric-grid--3 mt-2">
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Quotes MTD') }}</span><span class="exec-metric__value">{{ $sales['quotes_mtd'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Orders MTD') }}</span><span class="exec-metric__value">{{ $sales['orders_mtd'] }}</span></div>
        <div class="exec-metric"><span class="exec-metric__label">{{ __('Conversion') }}</span><span class="exec-metric__value">{{ $sales['conversion_rate'] }}%</span></div>
    </div>
</section>
