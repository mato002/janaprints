<x-admin-layout :title="__('Input VAT')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Input VAT')]]">
    <x-admin.page-header :title="__('Input VAT')" />
    @include('admin.tax.partials.report-filters')
    @include('admin.tax.partials.direction-report', ['report' => $report])
</x-admin-layout>
