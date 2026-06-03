<x-admin-layout :title="__('Stock receipt')">
    <x-admin.page-header :title="__('New stock receipt')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.receipts.store') }}" class="space-y-4">
            @csrf
            @include('admin.inventory.partials.document-header', ['type' => 'receipt', 'warehouses' => $warehouses, 'sources' => $sources])
            @include('admin.inventory.partials.line-items', ['items' => $items])
            <button class="erp-btn-primary">{{ __('Save draft') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
