<x-admin-layout :title="__('Edit Price List')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Price Lists'), 'url' => route('admin.inventory.catalogue.price-lists.index')], ['label' => __('Edit')]]">
    <x-admin.page-header :title="$priceList->name" />
    <x-admin.card><form method="POST" action="{{ route('admin.inventory.catalogue.price-lists.update', $priceList) }}" class="space-y-4">@csrf @method('PUT') @include('admin.inventory.catalogue.price-lists.partials.form', ['priceList' => $priceList])<button class="erp-btn-primary">{{ __('Save') }}</button></form></x-admin.card>
</x-admin-layout>
