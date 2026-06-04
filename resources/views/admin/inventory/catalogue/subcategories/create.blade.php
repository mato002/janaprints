<x-admin-layout :title="__('New Subcategory')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Subcategories'), 'url' => route('admin.inventory.catalogue.subcategories.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New Subcategory')" />
    <x-admin.card><form method="POST" action="{{ route('admin.inventory.catalogue.subcategories.store') }}" class="space-y-4 max-w-3xl">@csrf @include('admin.inventory.catalogue.subcategories.partials.form')<button class="erp-btn-primary">{{ __('Save') }}</button></form></x-admin.card>
</x-admin-layout>
