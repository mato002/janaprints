<x-admin-layout :title="__('Job cost') . ' — ' . $jobCard->job_card_number" :breadcrumbs="[['label' => __('Costing'), 'url' => route('admin.production.costing.dashboard')], ['label' => $jobCard->job_card_number]]">
    <x-admin.page-header :title="__('Job cost sheet')" :description="$jobCard->job_card_number" />
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Revenue')" :value="number_format($costSheet->revenue, 2)" icon="cash" />
        <x-admin.kpi-widget :label="__('Total cost')" :value="number_format($costSheet->total_cost, 2)" icon="receipt-tax" />
        <x-admin.kpi-widget :label="__('Gross profit')" :value="number_format($costSheet->gross_profit, 2)" icon="trending-up" />
        <x-admin.kpi-widget :label="__('Margin')" :value="$costSheet->gross_margin_percent . '%'" icon="chart-pie" />
    </div>
    <x-admin.card class="mt-6">
        <table class="erp-table text-sm">
            <thead><tr><th>{{ __('Category') }}</th><th>{{ __('Description') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Unit') }}</th><th>{{ __('Total') }}</th></tr></thead>
            <tbody>
                @foreach ($costSheet->lines as $line)
                    <tr>
                        <td>{{ str($line->cost_category->value)->headline() }}</td>
                        <td>{{ $line->description }}</td>
                        <td>{{ $line->quantity }}</td>
                        <td>{{ number_format($line->unit_cost, 2) }}</td>
                        <td>{{ number_format($line->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
