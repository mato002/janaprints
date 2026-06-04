<x-admin-layout :title="__('New item')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue')], ['label' => __('Items'), 'url' => route('admin.inventory.items.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New inventory item')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.items.store') }}" class="space-y-4 max-w-xl">
            @csrf
            @include('admin.inventory.items.partials.form')
            <button class="erp-btn-primary">{{ __('Save') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
