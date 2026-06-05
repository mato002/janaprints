<x-admin-layout :title="__('Stock receipt')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Goods Receiving'), 'url' => route('admin.inventory.receipts.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New stock receipt')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.receipts.store') }}" class="space-y-4">
            @csrf
            @include('admin.inventory.partials.document-header', ['type' => 'receipt', 'warehouses' => $warehouses, 'sources' => $sources, 'formFields' => $formFields])
            @include('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'dynamic' => true])
            <button class="erp-btn-primary">{{ __('Save draft') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
