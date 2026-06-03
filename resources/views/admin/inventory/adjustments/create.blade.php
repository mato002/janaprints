<x-admin-layout :title="__('Stock adjustment')">
    <x-admin.page-header :title="__('New adjustment')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.adjustments.store') }}" class="space-y-4">
            @csrf
            @include('admin.inventory.partials.document-header', ['type' => 'adjustment', 'warehouses' => $warehouses])
            @include('admin.inventory.partials.line-items', ['items' => $items, 'directions' => $directions])
            <button class="erp-btn-primary">{{ __('Save draft') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
