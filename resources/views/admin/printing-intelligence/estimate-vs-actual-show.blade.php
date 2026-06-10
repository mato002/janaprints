<x-admin-layout :title="__('Comparison #:id', ['id' => $comparison->id])" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Estimate vs Actual'), 'url' => route('admin.printing-intelligence.estimate-vs-actual', ['tab' => 'comparisons'])],
    ['label' => '#'.$comparison->id],
]">
    <x-admin.page-header :title="__('Estimate vs Actual Detail')" :description="__('Side-by-side estimated vs actual cost comparison.')">
        <span class="erp-badge">{{ $comparison->comparison_status?->label() }}</span>
        <span class="erp-badge">{{ $comparison->variance_class?->label() }}</span>
    </x-admin.page-header>

    @include('admin.printing-intelligence.partials.nav')

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-6">
        <x-admin.kpi-widget :label="__('Accuracy score')" :value="$comparison->accuracy_score !== null ? number_format((float) $comparison->accuracy_score, 1).'%' : '—'" icon="chart-bar" />
        <x-admin.kpi-widget :label="__('Total variance %')" :value="$comparison->total_cost_variance_percent !== null ? number_format((float) $comparison->total_cost_variance_percent, 1).'%' : '—'" icon="scale" />
        <x-admin.kpi-widget :label="__('Recommended price')" :value="$comparison->recommended_price !== null ? number_format((float) $comparison->recommended_price, 2) : '—'" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Actual selling price')" :value="$comparison->actual_selling_price !== null ? number_format((float) $comparison->actual_selling_price, 2) : '—'" icon="receipt-tax" />
    </div>

    <x-admin.card class="mb-6">
        <h3 class="font-medium mb-3">{{ __('Cost breakdown') }}</h3>
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Estimated') }}</th>
                    <th>{{ __('Actual') }}</th>
                    <th>{{ __('Variance') }}</th>
                    <th>{{ __('Variance %') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ([
                    __('Material') => ['est' => 'estimated_material_cost', 'act' => 'actual_material_cost', 'var' => 'material_cost_variance', 'pct' => 'material_cost_variance_percent'],
                    __('Ink') => ['est' => 'estimated_ink_cost', 'act' => 'actual_ink_cost', 'var' => 'ink_cost_variance', 'pct' => 'ink_cost_variance_percent'],
                    __('Machine / process') => ['est' => 'estimated_machine_cost', 'act' => 'actual_machine_cost', 'var' => 'machine_cost_variance', 'pct' => 'machine_cost_variance_percent'],
                    __('Labour') => ['est' => 'estimated_labour_cost', 'act' => 'actual_labour_cost', 'var' => 'labour_cost_variance', 'pct' => 'labour_cost_variance_percent'],
                    __('Overhead') => ['est' => 'estimated_overhead_cost', 'act' => 'actual_overhead_cost', 'var' => 'overhead_cost_variance', 'pct' => 'overhead_cost_variance_percent'],
                    __('Total') => ['est' => 'estimated_total_cost', 'act' => 'actual_total_cost', 'var' => 'total_cost_variance', 'pct' => 'total_cost_variance_percent'],
                ] as $label => $fields)
                    <tr @class(['font-medium' => $label === __('Total')])>
                        <td>{{ $label }}</td>
                        <td>{{ number_format((float) $comparison->{$fields['est']}, 2) }}</td>
                        <td>{{ number_format((float) $comparison->{$fields['act']}, 2) }}</td>
                        <td>{{ number_format((float) $comparison->{$fields['var']}, 2) }}</td>
                        <td>{{ $comparison->{$fields['pct']} !== null ? number_format((float) $comparison->{$fields['pct']}, 1).'%' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>

    @if ($comparison->recommendation)
        <x-admin.card class="mb-6">
            <h3 class="font-medium mb-2">{{ __('Advisory recommendation') }}</h3>
            <p class="text-sm text-slate-700">{{ $comparison->recommendation }}</p>
            <p class="mt-2 text-xs text-amber-700">{{ __('Advisory only — formulas, ink profiles, machine rates, and prices are not changed automatically.') }}</p>
        </x-admin.card>
    @endif

    @if (! empty($comparison->warnings))
        <x-admin.card class="mb-6">
            <h3 class="font-medium mb-2">{{ __('Warnings') }}</h3>
            <ul class="list-disc pl-5 text-sm text-amber-800 space-y-1">
                @foreach ($comparison->warnings as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif

    <x-admin.card>
        <h3 class="font-medium mb-3">{{ __('Linked records') }}</h3>
        <dl class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
            <div><dt class="text-xs text-slate-500">{{ __('Quotation') }}</dt><dd>{{ $comparison->quotation?->quotation_number ?? '—' }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Job card') }}</dt><dd>{{ $comparison->jobCard?->job_card_number ?? '—' }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Job cost sheet') }}</dt><dd>#{{ $comparison->job_cost_sheet_id ?? '—' }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Compared at') }}</dt><dd>{{ $comparison->compared_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
        </dl>
    </x-admin.card>
</x-admin-layout>
