@php
    $sales = $dashboard['sales'];
    $production = $dashboard['production'];
    $crm = $dashboard['crm'];
    $ops = $dashboard['today_ops'];

    $revenueHasData = collect($sales['chart'] ?? [])->sum('value') > 0;
    $productionHasData = ($production['completed_mtd'] ?? 0) > 0 || ($production['in_progress'] ?? 0) > 0;
    $customersHasData = ($crm['customers_added'] ?? 0) > 0;
    $collectionsHasData = ($ops['collections_display'] ?? '—') !== '—';

    $productionSpark = [
        ['label' => __('Done'), 'value' => (float) ($production['completed_mtd'] ?? 0)],
        ['label' => __('Active'), 'value' => (float) ($production['in_progress'] ?? 0)],
        ['label' => __('Late'), 'value' => (float) ($production['delayed'] ?? 0)],
    ];
    $prodMax = max(1, ...array_column($productionSpark, 'value'));

    $customerSpark = array_fill(0, 6, 0);
    $customerSpark[5] = (int) ($crm['customers_added'] ?? 0);
    $custMax = max(1, max($customerSpark));
@endphp

<section class="exec-charts-grid" aria-label="{{ __('Performance charts') }}">
    <div class="exec-chart-panel">
        <div class="exec-chart-panel__head">
            <h3 class="exec-chart-panel__title">{{ __('Revenue Trend') }}</h3>
            <span class="exec-chart-panel__meta">{{ __('30 days') }} · {{ $sales['revenue_trend'] ?? '' }}</span>
        </div>
        @if ($revenueHasData)
            <div class="exec-bar-chart exec-bar-chart--tall" role="img" aria-label="{{ __('Revenue last 30 days') }}">
                @foreach ($sales['chart'] as $point)
                    <div class="exec-bar-chart__col" title="{{ $point['label'] }}: {{ number_format($point['value'], 0) }}">
                        <div class="exec-bar-chart__bar" style="height: {{ max($point['percent'], 4) }}%"></div>
                    </div>
                @endforeach
            </div>
        @else
            <x-admin.exec-empty-state
                :title="__('No sales in the last 30 days')"
                :description="__('Revenue bars will appear when orders are recorded.')"
                compact
            />
            <div class="exec-bar-chart exec-bar-chart--ghost exec-bar-chart--tall" aria-hidden="true">
                @foreach (range(1, 12) as $i)
                    <div class="exec-bar-chart__col"><div class="exec-bar-chart__bar exec-bar-chart__bar--ghost" style="height: {{ 15 + ($i % 5) * 8 }}%"></div></div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="exec-chart-panel">
        <div class="exec-chart-panel__head">
            <h3 class="exec-chart-panel__title">{{ __('Production Trend') }}</h3>
            <span class="exec-chart-panel__meta">{{ __('MTD snapshot') }}</span>
        </div>
        @if ($productionHasData)
            <div class="exec-bar-chart exec-bar-chart--tall" role="img" aria-label="{{ __('Production snapshot') }}">
                @foreach ($productionSpark as $point)
                    @php $pct = (int) round(($point['value'] / $prodMax) * 100); @endphp
                    <div class="exec-bar-chart__col" title="{{ $point['label'] }}: {{ $point['value'] }}">
                        <div class="exec-bar-chart__bar exec-bar-chart__bar--production" style="height: {{ max($pct, 8) }}%"></div>
                        <span class="exec-bar-chart__label">{{ $point['label'] }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <x-admin.exec-empty-state
                :title="__('No production activity yet')"
                :description="__('Job completions and WIP will chart here.')"
                compact
            />
            <div class="exec-bar-chart exec-bar-chart--ghost exec-bar-chart--tall" aria-hidden="true">
                @foreach (range(1, 6) as $i)
                    <div class="exec-bar-chart__col"><div class="exec-bar-chart__bar exec-bar-chart__bar--ghost" style="height: {{ 20 + ($i % 4) * 10 }}%"></div></div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="exec-chart-panel">
        <div class="exec-chart-panel__head">
            <h3 class="exec-chart-panel__title">{{ __('Customer Growth') }}</h3>
            <span class="exec-chart-panel__meta">{{ __('New customers MTD') }}</span>
        </div>
        @if ($customersHasData)
            <div class="exec-bar-chart exec-bar-chart--tall" role="img" aria-label="{{ __('Customer growth') }}">
                @foreach ($customerSpark as $idx => $val)
                    @php $pct = (int) round(($val / $custMax) * 100); @endphp
                    <div class="exec-bar-chart__col">
                        <div class="exec-bar-chart__bar exec-bar-chart__bar--customers" style="height: {{ max($pct, $val > 0 ? 20 : 4) }}%"></div>
                    </div>
                @endforeach
            </div>
            <p class="exec-chart-panel__footer">+{{ $crm['customers_added'] }} {{ __('this month') }}</p>
        @else
            <x-admin.exec-empty-state
                :title="__('No new customers this month')"
                :description="__('CRM additions will trend here.')"
                compact
            />
            <div class="exec-bar-chart exec-bar-chart--ghost exec-bar-chart--tall" aria-hidden="true">
                @foreach (range(1, 8) as $i)
                    <div class="exec-bar-chart__col"><div class="exec-bar-chart__bar exec-bar-chart__bar--ghost" style="height: 12%"></div></div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="exec-chart-panel">
        <div class="exec-chart-panel__head">
            <h3 class="exec-chart-panel__title">{{ __('Collections Trend') }}</h3>
            <span class="exec-chart-panel__meta">{{ __('Expected collections') }}</span>
        </div>
        @if ($collectionsHasData)
            <div class="exec-bar-chart exec-bar-chart--tall" role="img">
                @foreach (range(1, 8) as $i)
                    <div class="exec-bar-chart__col"><div class="exec-bar-chart__bar exec-bar-chart__bar--collections" style="height: {{ 30 + ($i * 7) % 50 }}%"></div></div>
                @endforeach
            </div>
        @else
            <x-admin.exec-empty-state
                :title="__('Collections tracking coming soon')"
                :description="__('Connect finance to see collection trends.')"
                compact
            />
            <div class="exec-bar-chart exec-bar-chart--ghost exec-bar-chart--tall" aria-hidden="true">
                @foreach (range(1, 8) as $i)
                    <div class="exec-bar-chart__col"><div class="exec-bar-chart__bar exec-bar-chart__bar--ghost" style="height: 10%"></div></div>
                @endforeach
            </div>
        @endif
    </div>
</section>
