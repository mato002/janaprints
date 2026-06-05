<x-admin-layout :title="__('New Price Book')" :breadcrumbs="[['label' => __('Price Books'), 'url' => route('admin.commercial.price-books.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New price book')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.commercial.price-books.store') }}" class="space-y-4 p-4">
            @csrf
            @include('admin.commercial.price-books.form')
            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Create price book') }}</button>
                <a href="{{ route('admin.commercial.price-books.index') }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
