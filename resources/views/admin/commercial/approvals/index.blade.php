<x-admin-layout :title="__('Approvals')" :breadcrumbs="[['label' => __('Commercial')], ['label' => __('Approvals')]]">
    @include('admin.sales.desk.partials.desk-mode-nav', ['activeSalesView' => \App\Support\Sales\SalesDeskViews::APPROVALS])

    <x-admin.page-header :title="__('Commercial approvals queue')" :description="__('Pending quotations, sales orders, and artwork submissions.')" />

    @include('admin.commercial.approvals.partials.table', ['rows' => $sections['pending_quotations'], 'title' => __('Pending Quotations')])
    @include('admin.commercial.approvals.partials.table', ['rows' => $sections['pending_sales_orders'], 'title' => __('Pending Sales Orders')])
    @include('admin.commercial.approvals.partials.table', ['rows' => $sections['pending_artwork'], 'title' => __('Pending Artwork')])
    @include('admin.commercial.approvals.partials.table', ['rows' => $sections['recently_approved'], 'title' => __('Recently Approved')])
    @include('admin.commercial.approvals.partials.table', ['rows' => $sections['recently_rejected'], 'title' => __('Recently Rejected')])
</x-admin-layout>
