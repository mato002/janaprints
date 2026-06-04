<x-admin-layout :title="__('New Price List')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Price Lists'), 'url' => route('admin.inventory.catalogue.price-lists.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New Price List')" />
    <x-admin.card><form method="POST" action="{{ route('admin.inventory.catalogue.price-lists.store') }}" class="space-y-4">@csrf @include('admin.inventory.catalogue.price-lists.partials.form')<button class="erp-btn-primary">{{ __('Save') }}</button></form></x-admin.card>
</x-admin-layout>
