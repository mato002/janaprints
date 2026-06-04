<x-admin-layout :title="__('Customer ledger')">
    <x-admin.page-header :title="__('Customer ledger')" />

    <form method="GET" class="mb-6 flex flex-wrap gap-3 items-end">
        <div>
            <label class="erp-label">{{ __('Customer') }}</label>
            <select name="customer_id" class="erp-input" onchange="this.form.submit()">
                <option value="">{{ __('Select') }}</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" @selected($customerId == $c->id)>{{ $c->company_name }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="erp-label">{{ __('From') }}</label><input type="date" name="from_date" value="{{ request('from_date') }}" class="erp-input"></div>
        <div><label class="erp-label">{{ __('To') }}</label><input type="date" name="to_date" value="{{ request('to_date') }}" class="erp-input"></div>
        <button class="erp-btn-secondary">{{ __('Apply') }}</button>
    </form>

    @if ($report)
        <div class="mb-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <x-admin.kpi-widget :label="__('Opening')" :value="number_format($report['opening_balance'], 2)" />
            <x-admin.kpi-widget :label="__('Charges')" :value="number_format($report['total_charges'], 2)" />
            <x-admin.kpi-widget :label="__('Credits')" :value="number_format($report['total_credits'], 2)" />
            <x-admin.kpi-widget :label="__('Closing')" :value="number_format($report['closing_balance'], 2)" />
        </div>
        <x-admin.card>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] uppercase text-slate-400"><th>{{ __('Date') }}</th><th>{{ __('Type') }}</th><th>{{ __('Reference') }}</th><th>{{ __('Debit') }}</th><th>{{ __('Credit') }}</th><th>{{ __('Balance') }}</th></tr></thead>
                <tbody>
                    @foreach ($report['entries'] as $entry)
                        <tr class="border-t border-erp-border">
                            <td class="py-2">{{ $entry->date }}</td>
                            <td>{{ ucfirst($entry->type) }}</td>
                            <td class="font-mono">{{ $entry->reference }}</td>
                            <td class="font-mono">{{ $entry->debit > 0 ? number_format($entry->debit, 2) : '—' }}</td>
                            <td class="font-mono">{{ $entry->credit > 0 ? number_format($entry->credit, 2) : '—' }}</td>
                            <td class="font-mono">{{ number_format($entry->balance, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.card>
    @endif
</x-admin-layout>
