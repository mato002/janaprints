<x-admin-layout :title="__('Output VAT')" :breadcrumbs="[['label' => __('Accounting'), 'url' => route('admin.workspaces.accounting')], ['label' => __('Output VAT')]]">
    <x-admin.page-header :title="__('Output VAT')" />
    <x-admin.card class="mb-4">@include('admin.tax.partials.report-filters')</x-admin.card>
    @include('admin.tax.partials.direction-report', ['report' => $report])
</x-admin-layout>
