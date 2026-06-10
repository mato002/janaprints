@php
    $summary = $context['summary'] ?? [];
    $compositionPercent = $context['composition_percent'] ?? [];
    $tabs = [
        'overview' => __('Overview'),
        'composition' => __('Cost Composition'),
        'accuracy' => __('Estimate Accuracy'),
        'calibration' => __('Calibration'),
        'profitability' => __('Profitability'),
    ];
@endphp

<x-admin-layout :title="__('Cost Intelligence')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Cost Intelligence')],
]">
    <x-admin.page-header :title="__('Cost Intelligence')" :description="__('Cost composition, estimate accuracy, calibration, and profitability bridge. Read-only.')" />
    @include('admin.printing-intelligence.partials.nav')
    @include('admin.printing-intelligence.partials.workspace-tabs', ['tabs' => $tabs, 'activeTab' => $tab ?? 'overview', 'filters' => $filters ?? []])

    @if (($tab ?? 'overview') === 'overview')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-6">
            <x-admin.kpi-widget :label="__('Average Job Cost')" :value="($summary['average_job_cost'] ?? null) !== null ? number_format((float) $summary['average_job_cost'], 2) : '—'" icon="currency-dollar" />
            @can('printing.estimate-actual.view')
                <x-admin.kpi-widget :label="__('Average Accuracy')" :value="($summary['average_accuracy'] ?? null) !== null ? number_format((float) $summary['average_accuracy'], 1).'%' : '—'" icon="check-circle" />
                <x-admin.kpi-widget :label="__('Largest Variance Driver')" :value="$summary['largest_variance_driver']['label'] ?? '—'" icon="exclamation" />
            @endcan
            <x-admin.kpi-widget :label="__('Formula Version')" :value="$summary['formula_version'] ?? '—'" icon="document-text" />
        </div>
    @endif

    @if (in_array($tab ?? 'overview', ['overview', 'composition'], true))
        <x-admin.card class="mb-4">
            <h3 class="font-medium mb-3">{{ __('Cost composition (90d jobs)') }}</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm md:grid-cols-5">
                @foreach (['material' => __('Material'), 'ink' => __('Ink'), 'machine' => __('Machine'), 'labour' => __('Labour'), 'overhead' => __('Overhead')] as $key => $label)
                    <div>
                        <dt class="text-xs text-slate-500">{{ $label }}</dt>
                        <dd class="font-medium">{{ number_format((float) ($compositionPercent[$key] ?? 0), 1) }}%</dd>
                    </div>
                @endforeach
            </dl>
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'accuracy')
        @can('printing.estimate-actual.view')
            @if (isset($context['analytics']))
                <x-admin.card>
                    <p class="text-sm">{{ __('Comparisons: :count | Accurate: :pct%', [
                        'count' => $context['analytics']['summary']['comparison_count'] ?? 0,
                        'pct' => $context['analytics']['summary']['accurate_estimates_percent'] ?? '—',
                    ]) }}</p>
                </x-admin.card>
            @endif
        @else
            <x-admin.card><p class="text-sm text-slate-500">{{ __('Estimate vs Actual permission required.') }}</p></x-admin.card>
        @endcan
    @endif

    @if (($tab ?? 'overview') === 'calibration' && auth()->user()?->can('printing.calibration.view'))
        <x-admin.card>
            <p class="text-sm">{{ __('Pending calibration recommendations: :count', ['count' => count($context['calibration']['recommendations'] ?? [])]) }}</p>
        </x-admin.card>
    @endif

    @if (($tab ?? 'overview') === 'profitability' && auth()->user()?->can('printing.profitability.view'))
        <x-admin.card>
            <p class="text-sm">{{ __('Total profit (90d): :value', ['value' => number_format((float) ($context['profitability']['summary']['total_profit'] ?? 0), 2)]) }}</p>
        </x-admin.card>
    @endif
</x-admin-layout>
