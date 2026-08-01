@php
    use App\Support\Inventory\StoreDeskViews;
    use App\Support\Navigation\WorkspaceEmbed;
@endphp
<x-admin-layout :title="__('Store Balances')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Desk'), 'url' => StoreDeskViews::deskUrl()], ['label' => __('Balances')]]">
    @unless (WorkspaceEmbed::inWorkspaceContext())
        @include('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => StoreDeskViews::BALANCES])
    @endunless
    <x-admin.page-header :title="__('Store Balances')" :description="__('View stock position by item, warehouse, and branch.')" />
    @include('admin.inventory.store.partials.balances-content')
</x-admin-layout>
