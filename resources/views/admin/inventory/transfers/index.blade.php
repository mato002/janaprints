<x-admin-layout :title="__('Store Transfers')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Desk'), 'url' => route('admin.store.desk')], ['label' => __('Transfers')]]">
    @unless (\App\Support\Navigation\WorkspaceEmbed::inWorkspaceContext())
        @include('admin.store.desk.partials.desk-mode-nav', ['activeStoreView' => \App\Support\Inventory\StoreDeskViews::TRANSFERS])
    @endunless
    <x-admin.page-header :title="__('Store Transfers')" :description="__('Move stock between warehouses using controlled inventory movements.')">
        <x-slot name="actions">
            @if (auth()->user()?->can('inventory.transfer'))
                <a href="{{ route('admin.inventory.transfers.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('New transfer') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    @include('admin.inventory.transfers.partials.table')
</x-admin-layout>
