<x-admin-layout :title="__('Output VAT')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Output VAT')]]">
    <x-admin.page-header :title="__('Output VAT')" />
    @include('admin.tax.partials.report-filters')
    @include('admin.tax.partials.direction-report', ['report' => $report])
</x-admin-layout>
