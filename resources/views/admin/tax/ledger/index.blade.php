<x-admin-layout :title="__('Tax Ledger')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Tax Ledger')]]">
    <x-admin.page-header :title="__('Tax Ledger')" />

    <x-admin.card class="mb-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-[11px] text-slate-500">{{ __('Period') }}</label>
                <select name="tax_period_id" class="erp-input mt-1">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($periods as $period)
                        <option value="{{ $period->id }}" @selected(($filters['tax_period_id'] ?? null) == $period->id)>{{ $period->code }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-[11px] text-slate-500">{{ __('Direction') }}</label>
                <select name="direction" class="erp-input mt-1">
                    <option value="">{{ __('All') }}</option>
                    <option value="output" @selected(($filters['direction'] ?? '') === 'output')>{{ __('Output') }}</option>
                    <option value="input" @selected(($filters['direction'] ?? '') === 'input')>{{ __('Input') }}</option>
                </select>
            </div>
            <div><label class="text-[11px] text-slate-500">{{ __('From') }}</label><input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="erp-input mt-1"></div>
            <div><label class="text-[11px] text-slate-500">{{ __('To') }}</label><input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="erp-input mt-1"></div>
            <button class="erp-btn-primary">{{ __('Filter') }}</button>
        </form>
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
