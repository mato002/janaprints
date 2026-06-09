<x-admin-layout :title="__('Tax Ledger')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Tax Ledger')]]">
    <x-admin.page-header :title="__('Tax Ledger')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.tax.ledger.index')" :reset-url="route('admin.tax.ledger.index')">
            <select name="tax_period_id" class="erp-toolbar-select" aria-label="{{ __('Period') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($periods as $period)
                    <option value="{{ $period->id }}" @selected(($filters['tax_period_id'] ?? null) == $period->id)>{{ $period->code }}</option>
                @endforeach
            </select>
            <select name="direction" class="erp-toolbar-select" aria-label="{{ __('Direction') }}">
                <option value="">{{ __('All') }}</option>
                <option value="output" @selected(($filters['direction'] ?? '') === 'output')>{{ __('Output') }}</option>
                <option value="input" @selected(($filters['direction'] ?? '') === 'input')>{{ __('Input') }}</option>
            </select>
            <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
            <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase text-slate-400">
                    <th class="py-2">{{ __('Date') }}</th>
                    <th class="py-2">{{ __('Document') }}</th>
                    <th class="py-2">{{ __('Code') }}</th>
                    <th class="py-2">{{ __('Direction') }}</th>
                    <th class="py-2 text-right">{{ __('Tax') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr class="border-b border-erp-border/50">
                        <td class="py-2">{{ $row->document_date->format('Y-m-d') }}</td>
                        <td class="py-2 font-mono text-xs">{{ $row->document_number }}</td>
                        <td class="py-2">{{ $row->taxCode?->code }}</td>
                        <td class="py-2">{{ $row->direction->label() }}</td>
                        <td class="py-2 text-right">{{ number_format($row->tax_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
