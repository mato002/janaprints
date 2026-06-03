<x-admin-layout :title="__('New item')">
    <x-admin.page-header :title="__('New inventory item')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.items.store') }}" class="space-y-4 max-w-xl">
            @csrf
            @include('admin.inventory.items.partials.form')
            <button class="erp-btn-primary">{{ __('Save') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
