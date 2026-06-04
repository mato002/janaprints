<x-admin-layout :title="__('Edit warehouse')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Warehouses'), 'url' => route('admin.inventory.warehouses.index')], ['label' => __('Edit')]]">
    <div class="bg-white shadow rounded-lg p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.inventory.warehouses.update', $warehouse) }}">
            @csrf
            @method('PUT')
            @include('admin.inventory.warehouses.form')
            <div class="mt-6"><x-primary-button>{{ __('Update') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
