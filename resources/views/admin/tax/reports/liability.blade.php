<x-admin-layout :title="__('Tax Liability')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Tax Liability')]]">
    <x-admin.page-header :title="__('Tax Liability')" />
    @include('admin.tax.partials.report-filters', ['exportListing' => 'tax-liability'])
    <div class="grid grid-cols-4 gap-3">
        <x-admin.kpi-widget :label="__('Output VAT')" :value="number_format($report['output_vat'], 2)" />
        <x-admin.kpi-widget :label="__('Input VAT')" :value="number_format($report['input_vat'], 2)" />
        <x-admin.kpi-widget :label="__('Withholding')" :value="number_format($report['withholding_tax'], 2)" />
        <x-admin.kpi-widget :label="__('Net liability')" :value="number_format($report['net_liability'], 2)" />
    </div>
</x-admin-layout>
