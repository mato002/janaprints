<x-admin-layout :title="__('Sales approvals')" :breadcrumbs="[['label' => __('Sales Desk')], ['label' => __('Approvals')]]">
    @include('admin.sales.desk.partials.desk-mode-nav', ['activeSalesView' => \App\Support\Sales\SalesDeskViews::APPROVALS])
    @include('admin.commercial.approvals.partials.register-content')
</x-admin-layout>
