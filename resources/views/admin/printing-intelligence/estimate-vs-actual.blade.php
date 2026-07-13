<x-admin-layout :title="__('Estimate vs Actual')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Estimate vs Actual')],
]">
    <x-admin.page-header
        :title="__('Estimate vs Actual')"
        :description="__('Audit-safe comparison of Printing Intelligence estimates against actual production and job costing outcomes (PI6).')"
    >
        <x-slot name="export">
            <x-admin.export-dropdown :csv-url="route('admin.printing-intelligence.estimate-vs-actual.export', $filters ?? [])" />
        </x-slot>
        <x-slot name="actions">
            @can('printing.estimate-actual.compare')
                <form method="post" action="{{ route('admin.printing-intelligence.estimate-vs-actual.compare') }}">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Run batch comparison') }}</button>
                </form>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @include('admin.printing-intelligence.partials.nav')

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar
            :action="route('admin.printing-intelligence.estimate-vs-actual')"
            :reset-url="route('admin.printing-intelligence.estimate-vs-actual', ['tab' => $tab ?? 'overview'])"
        >
            <input type="hidden" name="tab" value="{{ $tab ?? 'overview' }}">
            <x-admin.filter-pill-date name="from" :label="__('From')" :value="$filters['from'] ?? ''" />
            <x-admin.filter-pill-date name="to" :label="__('To')" :value="$filters['to'] ?? ''" />
            <select name="variance_class" class="erp-toolbar-select" aria-label="{{ __('Variance class') }}">
                <option value="">{{ __('All variance classes') }}</option>
                @foreach (['accurate', 'minor', 'moderate', 'major', 'critical'] as $class)
                    <option value="{{ $class }}" @selected(($filters['variance_class'] ?? '') === $class)>{{ ucfirst($class) }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    @php
        $tabs = [
            'overview' => __('Overview'),
            'comparisons' => __('Comparisons'),
            'analytics' => __('Accuracy Analytics'),
            'drivers' => __('Variance Drivers'),
            'recommendations' => __('Recommendations'),
        ];
    @endphp

    <x-admin.card class="mb-4">
        <nav class="flex flex-wrap gap-2">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.printing-intelligence.estimate-vs-actual', array_merge($filters ?? [], ['tab' => $key])) }}"
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

    @if (($tab ?? 'overview') === 'overview')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7 mb-6">
            <x-admin.kpi-widget :label="__('Average accuracy score')" :value="$analytics['average_accuracy_score'] !== null ? number_format($analytics['average_accuracy_score'], 1).'%' : '—'" icon="chart-bar" />
            <x-admin.kpi-widget :label="__('Accurate estimates %')" :value="$analytics['accurate_estimates_percent'] !== null ? number_format($analytics['accurate_estimates_percent'], 1).'%' : '—'" icon="check-circle" />
            <x-admin.kpi-widget :label="__('Major variance count')" :value="$analytics['major_variance_count']" icon="exclamation" />
            <x-admin.kpi-widget :label="__('Unreliable estimates')" :value="$analytics['unreliable_estimates_count']" icon="x-circle" />
            <x-admin.kpi-widget :label="__('Avg total cost variance %')" :value="$analytics['average_total_cost_variance_percent'] !== null ? number_format($analytics['average_total_cost_variance_percent'], 1).'%' : '—'" icon="scale" />
            <x-admin.kpi-widget :label="__('Total underestimation')" :value="number_format($analytics['total_underestimation_value'], 2)" icon="arrow-up" />
            <x-admin.kpi-widget :label="__('Total overestimation')" :value="number_format($analytics['total_overestimation_value'], 2)" icon="arrow-down" />
        </div>

        <x-admin.card>
            <p class="text-sm text-slate-600">{{ __('Measurement and reporting only — no formulas, prices, or inventory are modified automatically.') }}</p>
            <p class="mt-2 text-xs text-slate-500">{{ __('Formula version: :version', ['version' => $config['estimate_actual_formula_version'] ?? 'PI6-V1']) }}</p>
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'comparisons')
        <x-admin.card>
            <div class="overflow-x-auto">
                <table class="erp-table text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Quotation') }}</th>
                            <th>{{ __('Job') }}</th>
                            <th>{{ __('Estimate total') }}</th>
                            <th>{{ __('Actual total') }}</th>
                            <th>{{ __('Variance %') }}</th>
                            <th>{{ __('Accuracy') }}</th>
                            <th>{{ __('Class') }}</th>
                            <th>{{ __('Compared') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comparisons as $row)
                            <tr>
                                <td>{{ $row->quotation?->quotation_number ?? '—' }}</td>
                                <td>{{ $row->jobCard?->job_card_number ?? '—' }}</td>
                                <td>{{ number_format((float) $row->estimated_total_cost, 2) }}</td>
                                <td>{{ number_format((float) $row->actual_total_cost, 2) }}</td>
                                <td>{{ $row->total_cost_variance_percent !== null ? number_format((float) $row->total_cost_variance_percent, 1).'%' : '—' }}</td>
                                <td>{{ $row->accuracy_score !== null ? number_format((float) $row->accuracy_score, 1).'%' : '—' }}</td>
                                <td><span class="erp-badge">{{ $row->variance_class?->label() ?? '—' }}</span></td>
                                <td>{{ $row->compared_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td><a class="text-sky-700 hover:underline" href="{{ route('admin.printing-intelligence.estimate-vs-actual.show', $row) }}">{{ __('View') }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center text-slate-500 py-6">{{ __('No comparisons recorded yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $comparisons->links() }}</div>
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'analytics')
        @can('printing.estimate-actual.analytics')
            <x-admin.card>
                <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                    <div><dt class="text-xs text-slate-500">{{ __('Comparisons') }}</dt><dd class="font-medium">{{ $analytics['comparison_count'] }} ({{ $analytics['completed_count'] }} {{ __('completed') }})</dd></div>
                    <div><dt class="text-xs text-slate-500">{{ __('Most underestimated') }}</dt><dd class="font-medium">{{ $analytics['most_underestimated_category'] ? ucfirst($analytics['most_underestimated_category']) : '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">{{ __('Most overestimated') }}</dt><dd class="font-medium">{{ $analytics['most_overestimated_category'] ? ucfirst($analytics['most_overestimated_category']) : '—' }}</dd></div>
                </dl>
            </x-admin.card>
        @else
            <x-admin.card><p class="text-sm text-slate-500">{{ __('Analytics permission required.') }}</p></x-admin.card>
        @endcan
    @endif

    @if (($tab ?? 'overview') === 'drivers')
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Top variance drivers') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($analytics['top_variance_drivers'] as $driver)
                    <li class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-2">
                        <span>#{{ $driver['comparison_id'] }} — {{ __('Job') }} #{{ $driver['production_job_card_id'] ?? '—' }}</span>
                        <span>{{ $driver['total_cost_variance_percent'] !== null ? number_format($driver['total_cost_variance_percent'], 1).'%' : '—' }}</span>
                        @if (! empty($driver['comparison_id']))
                            <a class="text-sky-700 hover:underline text-xs" href="{{ route('admin.printing-intelligence.estimate-vs-actual.show', $driver['comparison_id']) }}">{{ __('Details') }}</a>
                        @endif
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No variance drivers identified yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'recommendations')
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Advisory recommendations') }}</h3>
            <ul class="space-y-3 text-sm">
                @forelse ($comparisons as $row)
                    @if ($row->recommendation)
                        <li class="rounded-md border border-slate-200 p-3">
                            <div class="text-xs text-slate-500 mb-1">#{{ $row->id }} — {{ $row->compared_at?->format('Y-m-d') }}</div>
                            <p>{{ $row->recommendation }}</p>
                        </li>
                    @endif
                @empty
                    <li class="text-slate-500">{{ __('No recommendations available yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    @endif
</x-admin-layout>
