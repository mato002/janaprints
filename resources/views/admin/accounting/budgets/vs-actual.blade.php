<x-admin-layout :title="__('Budget vs Actual')" :breadcrumbs="[['label' => __('Budgets'), 'url' => route('admin.accounting.budgets.index')], ['label' => $budget->name, 'url' => route('admin.accounting.budgets.show', $budget)], ['label' => __('Vs actual')]]">
    <x-admin.page-header :title="__('Budget vs Actual')" :description="$budget->name.' · '.$budget->from_date->format('Y-m-d').' → '.$budget->to_date->format('Y-m-d')" />

    <div class="mb-4 grid grid-cols-3 gap-3">
        <x-admin.kpi-widget :label="__('Budget')" :value="number_format($report['totals']['budget'], 2)" />
        <x-admin.kpi-widget :label="__('Actual')" :value="number_format($report['totals']['actual'], 2)" />
        <x-admin.kpi-widget :label="__('Variance')" :value="number_format($report['totals']['variance'], 2)" />
    </div>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500 border-b border-erp-border">
                    <th class="py-2">{{ __('Account') }}</th>
                    <th class="py-2 text-right">{{ __('Budget') }}</th>
                    <th class="py-2 text-right">{{ __('Actual') }}</th>
                    <th class="py-2 text-right">{{ __('Variance') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['rows'] as $row)
                    <tr class="border-t border-erp-border">
                        <td class="py-2 font-mono text-xs">{{ $row['account_code'] }} — {{ $row['account_name'] }}</td>
                        <td class="py-2 text-right">{{ number_format($row['budget'], 2) }}</td>
                        <td class="py-2 text-right">{{ number_format($row['actual'], 2) }}</td>
                        <td class="py-2 text-right">{{ number_format($row['variance'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
