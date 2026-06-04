<x-admin-layout :title="__('VAT Summary')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('VAT Summary')]]">
    <x-admin.page-header :title="__('VAT Summary')" :description="__('From posted tax ledger transactions')" />

    <x-admin.card class="mb-4">
        @include('admin.tax.partials.report-filters')
    </x-admin.card>

    <div class="mb-4 grid grid-cols-4 gap-3">
        <x-admin.kpi-widget :label="__('Output VAT')" :value="number_format($report['output_vat'], 2)" />
        <x-admin.kpi-widget :label="__('Input VAT')" :value="number_format($report['input_vat'], 2)" />
        <x-admin.kpi-widget :label="__('Withholding')" :value="number_format($report['withholding_tax'], 2)" />
        <x-admin.kpi-widget :label="__('Net liability')" :value="number_format($report['net_liability'], 2)" />
    </div>

    <x-admin.card :title="__('By tax code')">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase text-slate-400">
                    <th class="py-2">{{ __('Code') }}</th>
                    <th class="py-2">{{ __('Direction') }}</th>
                    <th class="py-2 text-right">{{ __('Taxable') }}</th>
                    <th class="py-2 text-right">{{ __('Tax') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($report['by_code'] as $row)
                    <tr class="border-b border-erp-border/50">
                        <td class="py-2 font-mono text-xs">{{ $row['tax_code'] }}</td>
                        <td class="py-2">{{ $row['direction'] }}</td>
                        <td class="py-2 text-right">{{ number_format($row['taxable_amount'], 2) }}</td>
                        <td class="py-2 text-right">{{ number_format($row['tax_amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
