<x-admin-layout :title="$taxReturn->return_number" :breadcrumbs="[['label' => __('Tax Returns'), 'url' => route('admin.tax.returns.index')], ['label' => $taxReturn->return_number]]">
    <x-admin.page-header :title="$taxReturn->return_number" :description="$taxReturn->taxPeriod?->name">
        @if ($taxReturn->status->value === 'draft')
            <form method="POST" action="{{ route('admin.tax.returns.file', $taxReturn) }}">
                @csrf
                <button class="erp-btn-primary">{{ __('Mark filed') }}</button>
            </form>
        @endif
    </x-admin.page-header>

    <div class="grid grid-cols-4 gap-3">
        <x-admin.kpi-widget :label="__('Output tax')" :value="number_format($taxReturn->output_tax, 2)" />
        <x-admin.kpi-widget :label="__('Input tax')" :value="number_format($taxReturn->input_tax, 2)" />
        <x-admin.kpi-widget :label="__('Withholding')" :value="number_format($taxReturn->withholding_tax, 2)" />
        <x-admin.kpi-widget :label="__('Net liability')" :value="number_format($taxReturn->net_liability, 2)" />
    </div>
</x-admin-layout>
