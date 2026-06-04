<x-admin-layout :title="__('Store Balances')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Warehouses'), 'url' => route('admin.inventory.warehouses.index')], ['label' => __('Store Balances')]]">
    <x-admin.page-header :title="__('Store Balances')" :description="$warehouse->name">
        <x-slot name="actions">
            <a href="{{ route('admin.inventory.warehouses.show', $warehouse) }}" class="erp-btn-secondary">{{ __('Store profile') }}</a>
        </x-slot>
    </x-admin.page-header>

    @include('admin.inventory.warehouses.partials.balance-table', ['balances' => $balances])
</x-admin-layout>
