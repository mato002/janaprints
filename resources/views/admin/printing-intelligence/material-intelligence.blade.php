@php
    $summary = $context['summary'] ?? [];
    $materials = $context['materials'] ?? [];
    $deadStock = $context['dead_stock'] ?? [];
    $tabs = [
        'overview' => __('Overview'),
        'materials' => __('Materials'),
        'cost-trends' => __('Cost Trends'),
        'velocity' => __('Velocity'),
        'dead-stock' => __('Dead Stock'),
        'forecasting' => __('Forecasting'),
    ];
@endphp

<x-admin-layout :title="__('Material Intelligence')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Material Intelligence')],
]">
    <x-admin.page-header :title="__('Material Intelligence')" :description="__('Material costs, velocity, dead stock, and inventory risk. Read-only.')" />
    @include('admin.printing-intelligence.partials.nav')
    @include('admin.printing-intelligence.partials.workspace-tabs', ['tabs' => $tabs, 'activeTab' => $tab ?? 'overview', 'filters' => $filters ?? []])

    @if (($tab ?? 'overview') === 'overview')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-6">
            <x-admin.kpi-widget :label="__('Top Material Cost')" :value="$summary['top_material_cost']['name'] ?? '—'" icon="currency-dollar" />
            <x-admin.kpi-widget :label="__('Highest Velocity Material')" :value="$summary['highest_velocity']['name'] ?? '—'" icon="switch-horizontal" />
            <x-admin.kpi-widget :label="__('Dead Stock Value')" :value="number_format((float) ($summary['dead_stock_value'] ?? 0), 2)" icon="archive" />
            <x-admin.kpi-widget :label="__('Material Risk Level')" :value="$summary['material_risk_level']['risk_class'] ?? '—'" icon="exclamation" />
        </div>
    @endif

    @if (($tab ?? 'overview') === 'dead-stock')
        <x-admin.card>
            <div class="overflow-x-auto">
                <table class="erp-table text-sm">
                    <thead><tr><th>{{ __('Material') }}</th><th>{{ __('Balance') }}</th><th>{{ __('Value') }}</th><th>{{ __('Days inactive') }}</th></tr></thead>
                    <tbody>
                        @forelse ($deadStock as $row)
                            <tr>
                                <td>{{ $row['name'] ?? '—' }}</td>
                                <td>{{ number_format((float) ($row['balance'] ?? 0), 2) }}</td>
                                <td>{{ number_format((float) ($row['estimated_value'] ?? 0), 2) }}</td>
                                <td>{{ $row['days_inactive'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-slate-500">{{ __('No dead stock detected.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @else
        <x-admin.card>
            <div class="overflow-x-auto">
                <table class="erp-table text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Material') }}</th>
                            <th>{{ __('Current Cost') }}</th>
                            <th>{{ __('Stock') }}</th>
                            <th>{{ __('Velocity') }}</th>
                            <th>{{ __('Forecast Risk') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materials as $row)
                            <tr>
                                <td>{{ $row['name'] ?? '—' }}</td>
                                <td>{{ number_format((float) ($row['current_cost'] ?? 0), 2) }}</td>
                                <td>{{ number_format((float) ($row['stock'] ?? 0), 2) }}</td>
                                <td>{{ $row['velocity_class'] ?? '—' }}</td>
                                <td>{{ $row['forecast_risk'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No materials tracked.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @endif
</x-admin-layout>
