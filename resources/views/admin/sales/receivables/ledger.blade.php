<x-admin-layout :title="__('Customer ledger')">
    <x-admin.page-header :title="__('Customer ledger')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.sales.receivables.ledger')" :reset-url="route('admin.sales.receivables.ledger')">
            <select name="customer_id" class="erp-toolbar-select min-w-[12rem]" aria-label="{{ __('Customer') }}">
                <option value="">{{ __('Select customer') }}</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" @selected($customerId == $c->id)>{{ $c->company_name }}</option>
                @endforeach
            </select>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        </x-admin.index-toolbar>
    </x-admin.card>

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
