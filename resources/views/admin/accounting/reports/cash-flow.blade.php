<x-admin-layout :title="__('Cash Flow Statement')">
    <x-admin.page-header :title="__('Cash Flow Statement')" :description="__(':from to :to — posted journals only', ['from' => $report['from_date'], 'to' => $report['to_date']])" />

    @include('admin.accounting.partials.period-range-toolbar', [
        'action' => route('admin.accounting.reports.cash-flow'),
        'resetUrl' => route('admin.accounting.reports.cash-flow'),
        'filters' => $filters,
        'periods' => $periods,
        'customPeriodLabel' => __('Custom'),
    ])

    <div class="mb-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
        <x-admin.kpi-widget :label="__('Opening cash')" :value="number_format($report['opening_cash'], 2)" />
        <x-admin.kpi-widget :label="__('Net change')" :value="number_format($report['period_net_change'], 2)" />
        <x-admin.kpi-widget :label="__('Closing cash')" :value="number_format($report['closing_cash'], 2)" />
        <x-admin.kpi-widget :label="__('Net from activities')" :value="number_format($report['net_from_activities'], 2)" />
    </div>

    @foreach ($report['sections'] as $section)
        <x-admin.card class="mb-4">
            <h3 class="font-medium mb-2">
                {{ $section['label'] }} —
                {{ number_format($section['net'], 2) }}
            </h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-500 border-b border-erp-border">
                        <th class="py-2">{{ __('Counter account') }}</th>
                        <th class="py-2 text-right">{{ __('Inflows') }}</th>
                        <th class="py-2 text-right">{{ __('Outflows') }}</th>
                        <th class="py-2 text-right">{{ __('Net') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($section['lines'] as $line)
                        <tr class="border-t border-erp-border">
                            <td class="py-2 font-mono text-xs">{{ $line['label'] }}</td>
                            <td class="py-2 text-right">{{ number_format($line['inflow'], 2) }}</td>
                            <td class="py-2 text-right">{{ number_format($line['outflow'], 2) }}</td>
                            <td class="py-2 text-right">{{ number_format($line['net'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-3 text-sm text-slate-500">{{ __('No cash movements in this section.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-admin.card>
    @endforeach
</x-admin-layout>
