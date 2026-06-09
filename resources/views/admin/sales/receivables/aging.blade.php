<x-admin-layout :title="__('Aging analysis')">
    <x-admin.page-header :title="__('Accounts receivable aging')" :description="__('As of :date', ['date' => $report['as_of_date']])" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.sales.receivables.aging')" :reset-url="route('admin.sales.receivables.aging')">
            <input type="date" name="as_of_date" value="{{ $report['as_of_date'] }}" class="erp-toolbar-input" aria-label="{{ __('As of date') }}">
            <select name="customer_id" class="erp-toolbar-select" aria-label="{{ __('Customer') }}">
                <option value="">{{ __('All customers') }}</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->company_name }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase text-slate-400">
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Current') }}</th>
                    <th>1–30</th>
                    <th>31–60</th>
                    <th>61–90</th>
                    <th>90+</th>
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['rows'] as $row)
                    <tr class="border-t border-erp-border">
                        <td class="py-2 font-medium">{{ $row['customer_name'] }}</td>
                        <td class="font-mono">{{ number_format($row['current'], 2) }}</td>
                        <td class="font-mono">{{ number_format($row['days_1_30'], 2) }}</td>
                        <td class="font-mono">{{ number_format($row['days_31_60'], 2) }}</td>
                        <td class="font-mono">{{ number_format($row['days_61_90'], 2) }}</td>
                        <td class="font-mono">{{ number_format($row['days_90_plus'], 2) }}</td>
                        <td class="font-mono font-medium">{{ number_format($row['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
