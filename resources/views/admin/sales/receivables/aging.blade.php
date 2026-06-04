<x-admin-layout :title="__('Aging analysis')">
    <x-admin.page-header :title="__('Accounts receivable aging')" :description="__('As of :date', ['date' => $report['as_of_date']])" />

    <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
        <div><label class="erp-label">{{ __('As of') }}</label><input type="date" name="as_of_date" value="{{ $report['as_of_date'] }}" class="erp-input"></div>
        <div>
            <label class="erp-label">{{ __('Customer') }}</label>
            <select name="customer_id" class="erp-input">
                <option value="">{{ __('All') }}</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" @selected(request('customer_id') == $c->id)>{{ $c->company_name }}</option>
                @endforeach
            </select>
        </div>
        <button class="erp-btn-secondary">{{ __('Refresh') }}</button>
    </form>

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
                <tr class="border-t-2 border-erp-border font-semibold">
                    <td class="py-2">{{ __('Totals') }}</td>
                    <td class="font-mono">{{ number_format($report['totals']['current'], 2) }}</td>
                    <td class="font-mono">{{ number_format($report['totals']['days_1_30'], 2) }}</td>
                    <td class="font-mono">{{ number_format($report['totals']['days_31_60'], 2) }}</td>
                    <td class="font-mono">{{ number_format($report['totals']['days_61_90'], 2) }}</td>
                    <td class="font-mono">{{ number_format($report['totals']['days_90_plus'], 2) }}</td>
                    <td class="font-mono">{{ number_format($report['grand_total'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
