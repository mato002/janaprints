<x-admin-layout :title="$taxReturn->return_number" :breadcrumbs="[['label' => __('Tax Returns'), 'url' => route('admin.tax.returns.index')], ['label' => $taxReturn->return_number]]">
    <x-admin.page-header :title="$taxReturn->return_number" :description="$taxReturn->taxPeriod?->name">
        <div class="flex flex-wrap gap-2">
            @if ($taxReturn->status->value === 'draft')
                <form method="POST" action="{{ route('admin.tax.returns.file', $taxReturn) }}">
                    @csrf
                    <button class="erp-btn-primary">{{ __('File & generate package') }}</button>
                </form>
            @endif
            <a href="{{ route('admin.tax.returns.package', $taxReturn) }}" class="erp-btn-secondary">{{ __('Download filing package') }}</a>
        </div>
    </x-admin.page-header>

    <div class="grid grid-cols-4 gap-3 mb-4">
        <x-admin.kpi-widget :label="__('Output tax')" :value="number_format($taxReturn->output_tax, 2)" />
        <x-admin.kpi-widget :label="__('Input tax')" :value="number_format($taxReturn->input_tax, 2)" />
        <x-admin.kpi-widget :label="__('Withholding')" :value="number_format($taxReturn->withholding_tax, 2)" />
        <x-admin.kpi-widget :label="__('Net liability')" :value="number_format($taxReturn->net_liability, 2)" />
    </div>

    @if ($taxReturn->status->value === 'filed')
        <x-admin.card>
            <h3 class="font-medium mb-2">{{ __('Filing record') }}</h3>
            <p class="text-sm text-slate-600">{{ __('Filed at :when by :who', [
                'when' => optional($taxReturn->filed_at)->format('Y-m-d H:i'),
                'who' => $taxReturn->filedByUser?->name ?? __('Unknown'),
            ]) }}</p>
            @if ($taxReturn->filing_package_checksum)
                <p class="mt-2 font-mono text-xs text-slate-500">{{ __('Checksum') }}: {{ $taxReturn->filing_package_checksum }}</p>
            @endif
            <p class="mt-3 text-sm text-slate-500">{{ __('Download the JSON package and submit via KRA/eTIMS or your tax agent. Automatic KRA API submission is not enabled.') }}</p>
        </x-admin.card>
    @endif
</x-admin-layout>
