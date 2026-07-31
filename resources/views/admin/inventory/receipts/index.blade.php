<x-admin-layout :title="__('Receipts')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Desk'), 'url' => route('admin.store.desk')], ['label' => __('Receipts')]]">
    @unless (\App\Support\Navigation\WorkspaceEmbed::inWorkspaceContext())
        @include('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => \App\Support\Inventory\StoreDeskViews::RECEIPTS])
    @endunless
    <x-admin.page-header :title="__('Stock receipts')">
        @can('create', App\Models\Inventory\StockReceipt::class)
            <a href="{{ route('admin.inventory.receipts.create') }}" class="erp-btn-primary">{{ __('New receipt') }}</a>
        @endcan
    </x-admin.page-header>

    @include('admin.inventory.receipts.partials.table')
</x-admin-layout>
