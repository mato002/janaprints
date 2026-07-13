<x-admin-layout :title="__('Executive Intelligence')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Executive Intelligence')],
]">
    <x-admin.page-header
        :title="__('Executive Intelligence')"
        :description="__('Executive-level forecasting and scenario simulation (PI9). Read-only — no inventory, accounting, or pricing mutations.')"
    >
        <x-slot name="export">
            <x-admin.export-dropdown :csv-url="route('admin.printing-intelligence.executive-intelligence.export')" />
        </x-slot>
        <x-slot name="secondary">
            @can('printing.executive.forecast')
                <form method="post" action="{{ route('admin.printing-intelligence.executive.generate') }}">
                    @csrf
                    <button type="submit" class="erp-btn-secondary">{{ __('Generate forecasts') }}</button>
                </form>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @include('admin.printing-intelligence.partials.nav')

@php
        $tabs = [
            'dashboard' => __('Executive Dashboard'),
            'revenue' => __('Revenue Forecast'),
            'profit' => __('Profit Forecast'),
            'capacity' => __('Capacity Forecast'),
            'demand' => __('Demand Forecast'),
            'inventory' => __('Inventory Risk'),
            'customers' => __('Customer Trends'),
            'scenarios' => __('Scenario Simulation'),
            'alerts' => __('Executive Alerts'),
        ];
    @endphp

    <div class="mb-4">
        <x-admin.card>
            <nav class="flex flex-wrap gap-2">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('admin.printing-intelligence.executive-intelligence', array_merge($filters ?? [], ['tab' => $key])) }}"
                       @class([
                           'rounded-md px-3 py-1.5 text-xs font-medium',
                           'bg-slate-900 text-white' => ($tab ?? 'dashboard') === $key,
                           'bg-slate-100 text-slate-700 hover:bg-slate-200' => ($tab ?? 'dashboard') !== $key,
                       ])>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </x-admin.card>
    </div>

    @if (($tab ?? 'dashboard') === 'dashboard')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 mb-6">
            <x-admin.kpi-widget :label="__('Forecast Revenue')" :value="number_format((float) ($overview['forecast_revenue']['forecast_value'] ?? 0), 2)" icon="currency" />
            <x-admin.kpi-widget :label="__('Forecast Profit')" :value="number_format((float) ($overview['forecast_profit']['forecast_value'] ?? 0), 2)" icon="chart-bar" />
            <x-admin.kpi-widget :label="__('Forecast Margin')" :value="($overview['forecast_margin']['forecast_value'] ?? null) !== null ? number_format((float) $overview['forecast_margin']['forecast_value'], 1).'%' : '—'" icon="percent" />
            <x-admin.kpi-widget :label="__('Forecast Accuracy')" :value="($overview['forecast_accuracy'] ?? null) !== null ? number_format((float) $overview['forecast_accuracy'], 1).'%' : '—'" icon="check-circle" />
            <x-admin.kpi-widget :label="__('Capacity Utilization Forecast')" :value="($overview['capacity_utilization_forecast']['forecast_value'] ?? null) !== null ? number_format((float) $overview['capacity_utilization_forecast']['forecast_value'], 1).'%' : '—'" icon="cog" />
            <x-admin.kpi-widget :label="__('Top Growth Customer')" :value="$overview['top_growth_customer']['customer_name'] ?? '—'" icon="users" />
            <x-admin.kpi-widget :label="__('Top Demand Product')" :value="$overview['top_demand_product']['product_label'] ?? '—'" icon="cube" />
            <x-admin.kpi-widget :label="__('Highest Inventory Risk')" :value="$overview['highest_inventory_risk']['label'] ?? '—'" icon="exclamation" />
            <x-admin.kpi-widget :label="__('Most Profitable Machine')" :value="$overview['most_profitable_machine']['machine_name'] ?? '—'" icon="cog" />
            <x-admin.kpi-widget :label="__('Customer Concentration Risk')" :value="($overview['customer_concentration_risk_percent'] ?? null) !== null ? number_format((float) $overview['customer_concentration_risk_percent'], 1).'%' : '—'" icon="scale" />
        </div>

        @if (! empty($overview['alerts']))
            <x-admin.card class="mb-4">
                <h3 class="font-medium mb-3">{{ __('Active Executive Alerts') }} ({{ $overview['alert_count'] ?? 0 }})</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($overview['alerts'] as $alert)
                        <li class="rounded border border-slate-200 px-3 py-2">
                            <span class="erp-badge mr-2">{{ ucfirst($alert['severity'] ?? 'info') }}</span>
                            <strong>{{ $alert['title'] ?? '' }}</strong> — {{ $alert['message'] ?? '' }}
                        </li>
                    @endforeach
                </ul>
            </x-admin.card>
        @endif

        <x-admin.card>
            <p class="text-sm text-slate-600">{{ __('Deterministic forecasting only — no AI, no automatic procurement, pricing, or production changes.') }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ __('Formula version: :version', ['version' => $config['forecast_formula_version'] ?? 'PI9-V1']) }}</p>
        </x-admin.card>
    @endif

    @if (($tab ?? 'dashboard') === 'revenue')
        @if ($hasExecutiveAnalytics ?? false)
            @if ($revenue)
                @include('admin.printing-intelligence.partials.executive-forecast-periods', ['data' => $revenue, 'metric' => __('Revenue')])
            @else
                <x-admin.card><p class="text-sm text-slate-500">{{ __('No revenue forecast data available yet.') }}</p></x-admin.card>
            @endif
        @else
            <x-admin.card><p class="text-sm text-slate-500">{{ __('Executive analytics permission required to view revenue forecasts.') }}</p></x-admin.card>
        @endif
    @endif

    @if (($tab ?? 'dashboard') === 'profit')
        @if ($hasExecutiveAnalytics ?? false)
            @if ($profit)
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-admin.card>
                        <h3 class="font-medium mb-3">{{ __('Forecast Profit') }}</h3>
                        @include('admin.printing-intelligence.partials.executive-forecast-single', ['forecast' => $profit['forecast_profit'] ?? []])
                    </x-admin.card>
                    <x-admin.card>
                        <h3 class="font-medium mb-3">{{ __('Forecast Margin') }}</h3>
                        @include('admin.printing-intelligence.partials.executive-forecast-single', ['forecast' => $profit['forecast_margin_percent'] ?? [], 'suffix' => '%'])
                    </x-admin.card>
                </div>
            @else
                <x-admin.card><p class="text-sm text-slate-500">{{ __('No profit forecast data available yet.') }}</p></x-admin.card>
            @endif
        @else
            <x-admin.card><p class="text-sm text-slate-500">{{ __('Executive analytics permission required to view profit forecasts.') }}</p></x-admin.card>
        @endif
    @endif

    @if (($tab ?? 'dashboard') === 'capacity')
        @if ($hasExecutiveAnalytics ?? false)
            @if ($capacity)
                <x-admin.card class="mb-4">
                    @include('admin.printing-intelligence.partials.executive-forecast-single', ['forecast' => $capacity['overall_utilization_forecast'] ?? [], 'suffix' => '%'])
                </x-admin.card>
                @include('admin.printing-intelligence.partials.executive-capacity-table', ['rows' => $capacity['machines'] ?? []])
            @else
                <x-admin.card><p class="text-sm text-slate-500">{{ __('No capacity forecast data available yet.') }}</p></x-admin.card>
            @endif
        @else
            <x-admin.card><p class="text-sm text-slate-500">{{ __('Executive analytics permission required to view capacity forecasts.') }}</p></x-admin.card>
        @endif
    @endif

    @if (($tab ?? 'dashboard') === 'demand')
        @if ($hasExecutiveAnalytics ?? false)
            @if ($demand)
                @include('admin.printing-intelligence.partials.executive-demand-table', ['rows' => $demand['products'] ?? []])
            @else
                <x-admin.card><p class="text-sm text-slate-500">{{ __('No demand forecast data available yet.') }}</p></x-admin.card>
            @endif
        @else
            <x-admin.card><p class="text-sm text-slate-500">{{ __('Executive analytics permission required to view demand forecasts.') }}</p></x-admin.card>
        @endif
    @endif

    @if (($tab ?? 'dashboard') === 'inventory')
        @if ($hasExecutiveAnalytics ?? false)
            @if ($inventory)
                @include('admin.printing-intelligence.partials.executive-inventory-risk-table', ['rows' => $inventory['categories'] ?? []])
            @else
                <x-admin.card><p class="text-sm text-slate-500">{{ __('No inventory risk forecast data available yet.') }}</p></x-admin.card>
            @endif
        @else
            <x-admin.card><p class="text-sm text-slate-500">{{ __('Executive analytics permission required to view inventory risk forecasts.') }}</p></x-admin.card>
        @endif
    @endif

    @if (($tab ?? 'dashboard') === 'customers')
        @if ($hasExecutiveAnalytics ?? false)
            @if ($customers)
                @include('admin.printing-intelligence.partials.executive-customer-trends-table', ['rows' => $customers['rankings'] ?? []])
            @else
                <x-admin.card><p class="text-sm text-slate-500">{{ __('No customer trend data available yet.') }}</p></x-admin.card>
            @endif
        @else
            <x-admin.card><p class="text-sm text-slate-500">{{ __('Executive analytics permission required to view customer trends.') }}</p></x-admin.card>
        @endif
    @endif

    @if (($tab ?? 'dashboard') === 'scenarios')
        @can('printing.executive.forecast')
            @if ($scenarios)
                <x-admin.card class="mb-4">
                    <form method="get" class="flex flex-wrap gap-2 items-end">
                        <input type="hidden" name="tab" value="scenarios">
                        <label class="text-sm">
                            <span class="block text-xs text-slate-500 mb-1">{{ __('Scenario') }}</span>
                            <select name="scenario" class="erp-input text-sm" onchange="this.form.submit()">
                                @foreach ($scenarios['available_scenarios'] ?? [] as $option)
                                    <option value="{{ $option['key'] }}" @selected(($filters['scenario'] ?? '') === $option['key'])>{{ $option['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                    </form>
                </x-admin.card>
                @include('admin.printing-intelligence.partials.executive-scenario-result', ['result' => $scenarios])
            @endif
        @else
            <x-admin.card><p class="text-sm text-slate-500">{{ __('Forecast permission required for scenario simulation.') }}</p></x-admin.card>
        @endcan
    @endif

    @if (($tab ?? 'dashboard') === 'alerts' && $alerts)
        <x-admin.card>
            @forelse ($alerts['alerts'] ?? [] as $alert)
                <div class="border-b border-slate-100 py-3 last:border-0">
                    <span class="erp-badge mr-2">{{ ucfirst($alert['severity'] ?? 'info') }}</span>
                    <strong>{{ $alert['title'] ?? '' }}</strong>
                    <p class="mt-1 text-sm text-slate-600">{{ $alert['message'] ?? '' }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500 py-4">{{ __('No executive alerts at this time.') }}</p>
            @endforelse
        </x-admin.card>
    @endif
</x-admin-layout>
