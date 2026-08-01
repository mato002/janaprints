@php
    use App\Support\Inventory\StoreDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;
@endphp
<x-admin-layout :title="__('Movements')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Desk'), 'url' => StoreDeskViews::deskUrl()], ['label' => __('Movements')]]">
    @unless (WorkspaceEmbed::inWorkspaceContext())
        @include('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => StoreDeskViews::MOVEMENTS])
    @endunless
    <x-admin.page-header :title="__('Inventory movements')" :description="__('Audit trail — source of stock truth.')" />
    @include('admin.inventory.movements.partials.table')
</x-admin-layout>
