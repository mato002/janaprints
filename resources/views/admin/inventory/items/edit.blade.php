<x-admin-layout :title="__('Edit item')">
    <x-admin.page-header :title="$item->item_name" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.items.update', $item) }}" class="space-y-4 max-w-xl">
            @csrf @method('PUT')
            @include('admin.inventory.items.partials.form', ['item' => $item])
            <button class="erp-btn-primary">{{ __('Save') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
