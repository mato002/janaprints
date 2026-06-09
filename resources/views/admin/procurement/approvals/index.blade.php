<x-admin-layout :title="__('Procurement Approvals')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Procurement'), 'url' => route('admin.procurement.dashboard')], ['label' => __('Approvals')]]">
    <x-admin.page-header :title="__('Procurement approvals')" :description="__('Pending, aging, escalated, and rejected procurement approval chains.')" />

    @include('admin.procurement.approvals.partials.table', ['rows' => $sections['pending'], 'title' => __('Pending Procurement Approvals')])
    @include('admin.procurement.approvals.partials.table', ['rows' => $sections['aging'], 'title' => __('Aging Approvals')])
    @include('admin.procurement.approvals.partials.table', ['rows' => $sections['escalated'], 'title' => __('Escalated Approvals')])
    @include('admin.procurement.approvals.partials.table', ['rows' => $sections['rejected'], 'title' => __('Rejected Approvals')])
</x-admin-layout>
