<x-admin-layout :title="__('Create warehouse')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Warehouses'), 'url' => route('admin.inventory.warehouses.index')], ['label' => __('Create')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.inventory.warehouses.store') }}">
            @csrf
            @include('admin.inventory.warehouses.form', ['warehouse' => null])
            <div class="mt-6"><x-primary-button>{{ __('Create') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
