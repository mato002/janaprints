<x-admin-layout :title="__('Catalogue')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue')]]">
    <x-admin.page-header :title="__('Catalogue')" :description="__('Print-industry categories, brands, attributes, images, and price lists.')">
        <x-slot name="actions">
            <a href="{{ route('admin.inventory.items.index') }}" class="erp-btn-secondary">{{ __('Items') }}</a>
            <a href="{{ route('admin.inventory.catalogue.price-lists.index') }}" class="erp-btn-primary">{{ __('Price Lists') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Categories'), 'value' => $stats['categories'], 'icon' => 'folder'],
            ['label' => __('Subcategories'), 'value' => $stats['subcategories'], 'icon' => 'template'],
            ['label' => __('Brands'), 'value' => $stats['brands'], 'icon' => 'badge-check'],
            ['label' => __('Items'), 'value' => $stats['items'], 'icon' => 'cube'],
            ['label' => __('Price Lists'), 'value' => $stats['price_lists'], 'icon' => 'banknote'],
            ['label' => __('Items Missing Images'), 'value' => $stats['missing_images'], 'icon' => 'image'],
            ['label' => __('Items Missing Prices'), 'value' => $stats['missing_prices'], 'icon' => 'tag'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <a class="erp-btn-secondary justify-center" href="{{ route('admin.inventory.catalogue.categories.index') }}">{{ __('Category Management') }}</a>
        <a class="erp-btn-secondary justify-center" href="{{ route('admin.inventory.catalogue.subcategories.index') }}">{{ __('Subcategories') }}</a>
        <a class="erp-btn-secondary justify-center" href="{{ route('admin.inventory.catalogue.brands.index') }}">{{ __('Brands') }}</a>
        <a class="erp-btn-secondary justify-center" href="{{ route('admin.inventory.catalogue.attributes.index') }}">{{ __('Attributes') }}</a>
        <a class="erp-btn-secondary justify-center" href="{{ route('admin.inventory.catalogue.units.index') }}">{{ __('Units of Measure') }}</a>
    </div>
</x-admin-layout>
