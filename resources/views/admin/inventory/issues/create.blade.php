<x-admin-layout :title="__('Stock issue')">
    <x-admin.page-header :title="__('New stock issue')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.issues.store') }}" class="space-y-4">
            @csrf
            @include('admin.inventory.partials.document-header', ['type' => 'issue', 'warehouses' => $warehouses, 'destinations' => $destinations])
            @include('admin.inventory.partials.line-items', ['items' => $items])
            <button class="erp-btn-primary">{{ __('Save draft') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
