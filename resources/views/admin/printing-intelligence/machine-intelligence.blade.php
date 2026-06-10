@php
    $summary = $context['summary'] ?? [];
    $machines = $context['machines'] ?? [];
    $tabs = [
        'overview' => __('Overview'),
        'profiles' => __('Machine Profiles'),
        'costing' => __('Machine Costing'),
        'utilization' => __('Machine Utilization'),
        'profitability' => __('Machine Profitability'),
        'forecasting' => __('Machine Forecasting'),
    ];
@endphp

<x-admin-layout :title="__('Machine Intelligence')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Machine Intelligence')],
]">
    <x-admin.page-header
        :title="__('Machine Intelligence')"
        :description="__('Machine cost profiles, utilization, profitability, and capacity forecasting. Read-only.')"
    />

    @include('admin.printing-intelligence.partials.nav')
    @include('admin.printing-intelligence.partials.workspace-tabs', ['tabs' => $tabs, 'activeTab' => $tab ?? 'overview', 'filters' => $filters ?? []])

    @if (($tab ?? 'overview') === 'overview')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-6">
            <x-admin.kpi-widget :label="__('Most Profitable Machine')" :value="$summary['most_profitable']['machine_name'] ?? '—'" icon="chart-bar" />
            <x-admin.kpi-widget :label="__('Highest Utilization')" :value="($summary['highest_utilization']['utilization_percent'] ?? null) !== null ? number_format((float) $summary['highest_utilization']['utilization_percent'], 1).'%' : '—'" icon="cog" />
            <x-admin.kpi-widget :label="__('Lowest Cost Machine')" :value="($summary['lowest_cost']['cost_per_hour'] ?? null) !== null ? number_format((float) $summary['lowest_cost']['cost_per_hour'], 2) : '—'" icon="currency-dollar" />
            <x-admin.kpi-widget :label="__('Capacity Bottleneck')" :value="$summary['capacity_bottleneck']['machine_name'] ?? '—'" icon="exclamation" />
        </div>
    @endif

    @if (($tab ?? 'overview') === 'forecasting')
        <x-admin.card class="mb-4">
            <h3 class="font-medium mb-2">{{ __('Capacity bottlenecks') }}</h3>
            <ul class="text-sm space-y-1">
                @forelse (($context['capacity']['bottlenecks'] ?? []) as $bottleneck)
                    <li>{{ $bottleneck['machine_name'] ?? __('Machine') }} — {{ number_format((float) ($bottleneck['forecast_utilization_percent'] ?? 0), 1) }}%</li>
                @empty
                    <li class="text-slate-500">{{ __('No bottlenecks detected.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    @elseif (($tab ?? 'overview') === 'utilization')
        <x-admin.card class="mb-4">
            <p class="text-sm">{{ __('Overall capacity utilization forecast: :pct%', ['pct' => number_format((float) ($context['capacity']['overall_utilization_forecast'] ?? 0), 1)]) }}</p>
        </x-admin.card>
    @elseif (($tab ?? 'overview') === 'costing')
        <x-admin.card class="mb-4">
            <p class="text-sm">{{ __('Average machine cost/hour: :value', ['value' => count($machines) ? number_format(collect($machines)->avg('cost_per_hour'), 2) : '—']) }}</p>
        </x-admin.card>
    @endif

    @php
        $showProfit = in_array($tab ?? 'overview', ['overview', 'profitability'], true);
        $showUtil = in_array($tab ?? 'overview', ['overview', 'utilization', 'forecasting'], true);
    @endphp
    @if (in_array($tab ?? 'overview', ['overview', 'profiles', 'costing', 'utilization', 'profitability', 'forecasting'], true))
        <x-admin.card>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-medium">{{ __('Machines') }}</h3>
                <span class="text-xs text-slate-500">{{ __('Read-only') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="erp-table text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Machine') }}</th>
                            <th>{{ __('Cost/Hour') }}</th>
                            <th>{{ __('Setup (min)') }}</th>
                            <th>{{ __('Output/Hr') }}</th>
                            @if ($showProfit)
                                <th>{{ __('Profit') }}</th>
                                <th>{{ __('Margin') }}</th>
                            @endif
                            @if ($showUtil)
                                <th>{{ __('Utilization') }}</th>
                                <th>{{ __('Forecast Util.') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($machines as $row)
                            <tr>
                                <td>{{ $row['machine_name'] ?? '—' }}</td>
                                <td>{{ number_format((float) ($row['cost_per_hour'] ?? 0), 2) }}</td>
                                <td>{{ $row['setup_minutes'] ?? '—' }}</td>
                                <td>{{ $row['output_per_hour'] ?? '—' }}</td>
                                @if ($showProfit)
                                    <td>{{ ($row['profit'] ?? null) !== null ? number_format((float) $row['profit'], 2) : '—' }}</td>
                                    <td>{{ ($row['margin_percent'] ?? null) !== null ? number_format((float) $row['margin_percent'], 1).'%' : '—' }}</td>
                                @endif
                                @if ($showUtil)
                                    <td>{{ ($row['utilization_percent'] ?? null) !== null ? number_format((float) $row['utilization_percent'], 1).'%' : '—' }}</td>
                                    <td>{{ ($row['forecast_utilization_percent'] ?? null) !== null ? number_format((float) $row['forecast_utilization_percent'], 1).'%' : '—' }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="8" class="py-6 text-center text-slate-500">{{ __('No machine profiles configured yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @endif
</x-admin-layout>
