<x-admin-layout :title="__('Stock adjustment')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Adjustments'), 'url' => route('admin.inventory.adjustments.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New adjustment')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.adjustments.store') }}" class="space-y-4">
            @csrf
            @include('admin.inventory.partials.document-header', ['type' => 'adjustment', 'warehouses' => $warehouses, 'formFields' => $formFields])
            @include('admin.inventory.partials.line-items', ['items' => $items, 'directions' => $directions, 'formFields' => $formFields, 'lineCount' => 5])
            <button class="erp-btn-primary">{{ __('Save draft') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
