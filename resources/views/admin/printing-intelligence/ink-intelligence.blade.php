@php
    $summary = $context['summary'] ?? [];
    $profiles = $context['profiles'] ?? [];
    $tabs = [
        'overview' => __('Overview'),
        'profiles' => __('Ink Profiles'),
        'coverage' => __('Coverage Analysis'),
        'costing' => __('Ink Costing'),
        'consumption' => __('Consumption Trends'),
        'forecasting' => __('Forecasting'),
    ];
@endphp

<x-admin-layout :title="__('Ink Intelligence')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Ink Intelligence')],
]">
    <x-admin.page-header :title="__('Ink Intelligence')" :description="__('Ink profile costing, coverage analysis, and consumption trends. Read-only.')" />
    @include('admin.printing-intelligence.partials.nav')
    @include('admin.printing-intelligence.partials.workspace-tabs', ['tabs' => $tabs, 'activeTab' => $tab ?? 'overview', 'filters' => $filters ?? []])

    @php
        $activeProfileCount = count($context['profiles'] ?? []);
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        @if ($activeProfileCount === 0)
            <x-admin.alert variant="warning" class="flex-1">
                {{ __('Ink estimation requires at least one active ink profile.') }}
            </x-admin.alert>
        @endif

        @can('printing.ink-profiles.view')
            <a href="{{ route('admin.printing-intelligence.ink-profiles.index') }}" class="erp-btn-secondary text-sm">
                {{ __('Manage Ink Profiles') }}
            </a>
        @endcan
    </div>

    @if (($tab ?? 'overview') === 'overview')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-6">
            <x-admin.kpi-widget :label="__('Highest Cost Ink')" :value="$summary['highest_cost']['name'] ?? '—'" icon="currency-dollar" />
            <x-admin.kpi-widget :label="__('Most Consumed Ink')" :value="$summary['most_consumed']['name'] ?? '—'" icon="color-swatch" />
            <x-admin.kpi-widget :label="__('Lowest Yield Ink')" :value="$summary['lowest_yield']['name'] ?? '—'" icon="exclamation" />
            <x-admin.kpi-widget :label="__('Highest Margin Risk')" :value="$summary['highest_margin_risk']['label'] ?? '—'" icon="scale" />
        </div>
    @endif

    @if (($tab ?? 'overview') === 'coverage')
        <x-admin.card><p class="text-sm text-slate-600">{{ __('Coverage analyses completed: :count', ['count' => $context['coverage_analysis_count'] ?? 0]) }}</p></x-admin.card>
    @endif

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Ink Profile') }}</th>
                        <th>{{ __('Cost/ml') }}</th>
                        <th>{{ __('Yield') }}</th>
                        <th>{{ __('Consumption') }}</th>
                        <th>{{ __('Forecast Usage') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($profiles as $row)
                        <tr>
                            <td>{{ $row['name'] ?? '—' }}</td>
                            <td>{{ ($row['cost_per_ml'] ?? null) !== null ? number_format((float) $row['cost_per_ml'], 4) : '—' }}</td>
                            <td>{{ ($row['yield_per_page'] ?? null) !== null ? number_format((float) $row['yield_per_page'], 4) : '—' }}</td>
                            <td>{{ $row['consumption_estimate_count'] ?? 0 }}</td>
                            <td>{{ ($row['forecast_usage'] ?? null) !== null ? number_format((float) $row['forecast_usage'], 1) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No ink profiles configured.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
