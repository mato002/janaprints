<x-admin-layout :title="__('Edit Price Book')" :breadcrumbs="[['label' => __('Price Books'), 'url' => route('admin.commercial.price-books.index')], ['label' => $priceBook->name]]">
    <x-admin.page-header :title="__('Edit price book')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.commercial.price-books.update', $priceBook) }}" class="space-y-4 p-4">
            @csrf
            @method('PUT')
            @include('admin.commercial.price-books.form', ['priceBook' => $priceBook])
            <div class="flex gap-2">
                <button type="submit" class="erp-btn-primary">{{ __('Save changes') }}</button>
                <a href="{{ route('admin.commercial.price-books.show', $priceBook) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
