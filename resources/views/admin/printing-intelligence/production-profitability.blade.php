<x-admin-layout :title="__('Production Profitability')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Production Profitability')],
]">
    <x-admin.page-header
        :title="__('Production Profitability')"
        :description="__('Analytical profitability intelligence by job, customer, machine, and product (PI8). Reporting only — no pricing or formula changes.')"
    >
        <x-slot name="export">
            <x-admin.export-dropdown :csv-url="route('admin.printing-intelligence.production-profitability.export', $filters ?? [])" />
        </x-slot>
        <x-slot name="secondary">
            @can('printing.profitability.generate')
                <form method="post" action="{{ route('admin.printing-intelligence.profitability.generate') }}">
                    @csrf
                    <button type="submit" class="erp-btn-secondary">{{ __('Generate snapshots') }}</button>
                </form>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @include('admin.printing-intelligence.partials.nav')

@php
        $tabs = [
            'overview' => __('Overview'),
            'jobs' => __('Jobs'),
            'customers' => __('Customers'),
            'machines' => __('Machines'),
            'products' => __('Products'),
            'leakage' => __('Margin Leakage'),
            'analytics' => __('Analytics'),
        ];
        $summary = $overview['summary'] ?? [];
    @endphp

    <div class="mb-4">
        <x-admin.card>
            <nav class="flex flex-wrap gap-2">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('admin.printing-intelligence.production-profitability', array_merge($filters ?? [], ['tab' => $key])) }}"
                       @class([
                           'rounded-md px-3 py-1.5 text-xs font-medium',
                           'bg-slate-900 text-white' => ($tab ?? 'overview') === $key,
                           'bg-slate-100 text-slate-700 hover:bg-slate-200' => ($tab ?? 'overview') !== $key,
                       ])>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </x-admin.card>
    </div>

    @if (($tab ?? 'overview') === 'overview')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8 mb-6">
            <x-admin.kpi-widget :label="__('Total Revenue')" :value="number_format((float) ($summary['total_revenue'] ?? 0), 2)" icon="currency" />
            <x-admin.kpi-widget :label="__('Total Cost')" :value="number_format((float) ($summary['total_cost'] ?? 0), 2)" icon="scale" />
            <x-admin.kpi-widget :label="__('Total Profit')" :value="number_format((float) ($summary['total_profit'] ?? 0), 2)" icon="chart-bar" />
            <x-admin.kpi-widget :label="__('Average Margin')" :value="($summary['average_margin'] ?? null) !== null ? number_format((float) $summary['average_margin'], 1).'%' : '—'" icon="percent" />
            <x-admin.kpi-widget :label="__('Excellent Jobs')" :value="$summary['excellent_jobs'] ?? 0" icon="check-circle" />
            <x-admin.kpi-widget :label="__('Loss-Making Jobs')" :value="$summary['loss_making_jobs'] ?? 0" icon="x-circle" />
            <x-admin.kpi-widget :label="__('Most Profitable Customer')" :value="$overview['most_profitable_customer']['customer_name'] ?? '—'" icon="users" />
            <x-admin.kpi-widget :label="__('Most Profitable Machine')" :value="$overview['most_profitable_machine']['machine_name'] ?? '—'" icon="cog" />
        </div>

        <div class="grid gap-4 lg:grid-cols-2 mb-6">
            <x-admin.card>
                <h3 class="font-medium mb-3">{{ __('Top 10 Profitable Jobs') }}</h3>
                @include('admin.printing-intelligence.partials.profitability-jobs-table', ['rows' => $overview['top_profitable_jobs'] ?? []])
            </x-admin.card>
            <x-admin.card>
                <h3 class="font-medium mb-3">{{ __('Top 10 Loss-Making Jobs') }}</h3>
                @include('admin.printing-intelligence.partials.profitability-jobs-table', ['rows' => $overview['top_loss_making_jobs'] ?? []])
            </x-admin.card>
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            <x-admin.card>
                <h3 class="font-medium mb-3">{{ __('Top Customers') }}</h3>
                @include('admin.printing-intelligence.partials.profitability-customers-table', ['rows' => $overview['top_customers'] ?? []])
            </x-admin.card>
            <x-admin.card>
                <h3 class="font-medium mb-3">{{ __('Top Machines') }}</h3>
                @include('admin.printing-intelligence.partials.profitability-machines-table', ['rows' => $overview['top_machines'] ?? []])
            </x-admin.card>
            <x-admin.card>
                <h3 class="font-medium mb-3">{{ __('Top Products') }}</h3>
                @include('admin.printing-intelligence.partials.profitability-products-table', ['rows' => $overview['top_products'] ?? []])
            </x-admin.card>
        </div>

        <x-admin.card class="mt-4">
            <p class="text-sm text-slate-600">{{ __('Analytical reporting only — no inventory, accounting, quotations, or pricing are modified automatically.') }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ __('Formula version: :version', ['version' => $config['profitability_formula_version'] ?? 'PI8-V1']) }}</p>
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'jobs')
        <x-admin.card>
            @include('admin.printing-intelligence.partials.profitability-jobs-table', ['rows' => $jobs['jobs'] ?? []])
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'customers')
        <x-admin.card>
            @include('admin.printing-intelligence.partials.profitability-customers-table', ['rows' => $customers['rankings'] ?? []])
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'machines')
        <x-admin.card>
            @include('admin.printing-intelligence.partials.profitability-machines-table', ['rows' => $machines['rankings'] ?? []])
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'products')
        <x-admin.card>
            @include('admin.printing-intelligence.partials.profitability-products-table', ['rows' => $products['rankings'] ?? []])
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'leakage')
        <x-admin.card class="mb-4">
            <h3 class="font-medium mb-3">{{ __('Top Profit Erosion Drivers') }}</h3>
            <div class="overflow-x-auto">
                <table class="erp-table text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Total variance') }}</th>
                            <th>{{ __('Avg variance %') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leakage['top_profit_erosion_drivers'] ?? [] as $driver)
                            <tr>
                                <td>{{ $driver['label'] ?? ucfirst($driver['category'] ?? '') }}</td>
                                <td>{{ number_format((float) ($driver['total_variance'] ?? 0), 2) }}</td>
                                <td>{{ ($driver['average_variance_percent'] ?? null) !== null ? number_format((float) $driver['average_variance_percent'], 1).'%' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-slate-500 py-6">{{ __('No variance comparisons available yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if (! empty($leakage['recommendations']))
                <ul class="mt-4 list-disc pl-5 text-sm text-slate-600 space-y-1">
                    @foreach ($leakage['recommendations'] as $note)
                        <li>{{ $note }}</li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'analytics')
        @can('printing.profitability.analytics')
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-5 mb-6">
                <x-admin.kpi-widget :label="__('Total Revenue')" :value="number_format((float) ($analytics['total_revenue'] ?? 0), 2)" icon="currency" />
                <x-admin.kpi-widget :label="__('Total Cost')" :value="number_format((float) ($analytics['total_cost'] ?? 0), 2)" icon="scale" />
                <x-admin.kpi-widget :label="__('Total Profit')" :value="number_format((float) ($analytics['total_profit'] ?? 0), 2)" icon="chart-bar" />
                <x-admin.kpi-widget :label="__('Average Margin')" :value="($analytics['average_margin'] ?? null) !== null ? number_format((float) $analytics['average_margin'], 1).'%' : '—'" icon="percent" />
                <x-admin.kpi-widget :label="__('Period granularity')" :value="ucfirst($analytics['period_granularity'] ?? 'month')" icon="calendar" />
            </div>
            <x-admin.card>
                <div class="overflow-x-auto">
                    <table class="erp-table text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Period') }}</th>
                                <th>{{ __('Revenue') }}</th>
                                <th>{{ __('Cost') }}</th>
                                <th>{{ __('Profit') }}</th>
                                <th>{{ __('Margin %') }}</th>
                                <th>{{ __('Accuracy') }}</th>
                                <th>{{ __('Jobs') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($analytics['series'] ?? [] as $row)
                                <tr>
                                    <td>{{ $row['period'] }}</td>
                                    <td>{{ number_format((float) $row['revenue'], 2) }}</td>
                                    <td>{{ number_format((float) $row['cost'], 2) }}</td>
                                    <td>{{ number_format((float) $row['profit'], 2) }}</td>
                                    <td>{{ ($row['margin_percent'] ?? null) !== null ? number_format((float) $row['margin_percent'], 1).'%' : '—' }}</td>
                                    <td>{{ ($row['accuracy'] ?? null) !== null ? number_format((float) $row['accuracy'], 1) : '—' }}</td>
                                    <td>{{ $row['job_count'] ?? 0 }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-slate-500 py-6">{{ __('No profitability series data yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin.card>
        @else
            <x-admin.card><p class="text-sm text-slate-500">{{ __('Analytics permission required.') }}</p></x-admin.card>
        @endcan
    @endif
</x-admin-layout>
