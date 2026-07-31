<x-admin-layout :title="__('Adjustments')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Desk'), 'url' => route('admin.store.desk')], ['label' => __('Adjustments')]]">
    @unless (\App\Support\Navigation\WorkspaceEmbed::inWorkspaceContext())
        @include('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => \App\Support\Inventory\StoreDeskViews::ADJUSTMENTS])
    @endunless
    <x-admin.page-header :title="__('Stock adjustments')">
        @can('create', App\Models\Inventory\StockAdjustment::class)
            <a href="{{ route('admin.inventory.adjustments.create') }}" class="erp-btn-primary">{{ __('New adjustment') }}</a>
        @endcan
    </x-admin.page-header>

    @include('admin.inventory.adjustments.partials.table')
</x-admin-layout>
