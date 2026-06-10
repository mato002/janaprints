@php
    $summary = $context['summary'] ?? [];
    $estimates = $context['estimates'] ?? [];
    if (($tab ?? 'overview') === 'applied') {
        $estimates = collect($estimates)->where('applied', true)->values()->all();
    }
    $tabs = [
        'overview' => __('Overview'),
        'estimates' => __('Estimates'),
        'applied' => __('Applied Estimates'),
        'accuracy' => __('Accuracy'),
        'profitability' => __('Profitability'),
    ];
@endphp

<x-admin-layout :title="__('Quotation Intelligence')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Quotation Intelligence')],
]">
    <x-admin.page-header :title="__('Quotation Intelligence')" :description="__('Quotation estimates, accuracy, and profitability intelligence. Read-only.')" />
    @include('admin.printing-intelligence.partials.nav')
    @include('admin.printing-intelligence.partials.workspace-tabs', ['tabs' => $tabs, 'activeTab' => $tab ?? 'overview', 'filters' => $filters ?? []])

    @if (($tab ?? 'overview') === 'overview')
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-6">
            <x-admin.kpi-widget :label="__('Average Recommended Margin')" :value="($summary['average_recommended_margin'] ?? null) !== null ? number_format((float) $summary['average_recommended_margin'], 1).'%' : '—'" icon="percent" />
            <x-admin.kpi-widget :label="__('Average Quote Accuracy')" :value="($summary['average_quote_accuracy'] ?? null) !== null ? number_format((float) $summary['average_quote_accuracy'], 1).'%' : '—'" icon="check-circle" />
            <x-admin.kpi-widget :label="__('Most Profitable Quote')" :value="$summary['most_profitable_quote']['quotation_number'] ?? '—'" icon="chart-bar" />
            <x-admin.kpi-widget :label="__('Most Underpriced Quote')" :value="$summary['most_underpriced_quote']['quotation_number'] ?? '—'" icon="exclamation" />
        </div>
    @endif

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Quotation') }}</th>
                        <th>{{ __('Estimated Cost') }}</th>
                        <th>{{ __('Recommended Price') }}</th>
                        <th>{{ __('Actual Cost') }}</th>
                        <th>{{ __('Actual Margin') }}</th>
                        <th>{{ __('Accuracy') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estimates as $row)
                        <tr>
                            <td>{{ $row['quotation_number'] ?? '—' }}</td>
                            <td>{{ number_format((float) ($row['estimated_cost'] ?? 0), 2) }}</td>
                            <td>{{ number_format((float) ($row['recommended_price'] ?? 0), 2) }}</td>
                            <td>{{ ($row['actual_cost'] ?? null) !== null ? number_format((float) $row['actual_cost'], 2) : '—' }}</td>
                            <td>{{ ($row['actual_margin_percent'] ?? null) !== null ? number_format((float) $row['actual_margin_percent'], 1).'%' : '—' }}</td>
                            <td>{{ ($row['accuracy_score'] ?? null) !== null ? number_format((float) $row['accuracy_score'], 1).'%' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-slate-500">{{ __('No quotation estimates recorded yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
