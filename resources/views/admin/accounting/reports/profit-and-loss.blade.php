<x-admin-layout :title="__('Profit & Loss')">
    <x-admin.page-header :title="__('Profit & Loss')" :description="__(':from to :to — posted journals only', ['from' => $report['from_date'], 'to' => $report['to_date']])" />

    @include('admin.accounting.partials.period-range-toolbar', [
        'action' => route('admin.accounting.reports.profit-and-loss'),
        'resetUrl' => route('admin.accounting.reports.profit-and-loss'),
        'filters' => $filters,
        'periods' => $periods,
        'customPeriodLabel' => __('Custom'),
        'exportListing' => 'profit-and-loss',
    ])

    <div class="mb-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
        <x-admin.kpi-widget :label="__('Revenue')" :value="number_format($report['total_revenue'], 2)" />
        <x-admin.kpi-widget :label="__('Cost of sales')" :value="number_format($report['total_cost_of_sales'], 2)" />
        <x-admin.kpi-widget :label="__('Gross profit')" :value="number_format($report['gross_profit'], 2)" />
        <x-admin.kpi-widget :label="__('Net profit')" :value="number_format($report['net_profit'], 2)" />
    </div>

    @foreach ($report['sections'] as $section)
        <x-admin.card class="mb-4">
            <h3 class="font-medium mb-2">{{ $section['label'] }} — {{ number_format($section['total'], 2) }}</h3>
            <table class="w-full text-sm">
                @foreach ($section['accounts'] as $account)
                    <tr class="border-t border-erp-border">
                        <td class="py-2 font-mono text-xs">{{ $account['account_code'] }} — {{ $account['account_name'] }}</td>
                        <td class="py-2 text-right">{{ number_format($account['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </table>
        </x-admin.card>
    @endforeach
</x-admin-layout>
