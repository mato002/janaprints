<div class="mb-4 grid grid-cols-2 gap-3">
    <x-admin.kpi-widget :label="__('Total tax')" :value="number_format($report['total_tax'], 2)" />
    <x-admin.kpi-widget :label="__('Total taxable')" :value="number_format($report['total_taxable'], 2)" />
</div>
<x-admin.card>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-erp-border text-left text-[11px] uppercase text-slate-400">
                <th class="py-2">{{ __('Date') }}</th>
                <th class="py-2">{{ __('Document') }}</th>
                <th class="py-2">{{ __('Code') }}</th>
                <th class="py-2 text-right">{{ __('Taxable') }}</th>
                <th class="py-2 text-right">{{ __('Tax') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($report['rows'] as $row)
                <tr class="border-b border-erp-border/50">
                    <td class="py-2">{{ $row['document_date'] }}</td>
                    <td class="py-2 font-mono text-xs">{{ $row['document_number'] }}</td>
                    <td class="py-2">{{ $row['tax_code'] }}</td>
                    <td class="py-2 text-right">{{ number_format($row['taxable_amount'], 2) }}</td>
                    <td class="py-2 text-right">{{ number_format($row['tax_amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-admin.card>
