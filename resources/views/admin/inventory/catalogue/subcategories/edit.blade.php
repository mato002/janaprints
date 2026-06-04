<x-admin-layout :title="__('Edit Subcategory')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Subcategories'), 'url' => route('admin.inventory.catalogue.subcategories.index')], ['label' => __('Edit')]]">
    <x-admin.page-header :title="$subcategory->name" />
    <x-admin.card><form method="POST" action="{{ route('admin.inventory.catalogue.subcategories.update', $subcategory) }}" class="space-y-4 max-w-3xl">@csrf @method('PUT') @include('admin.inventory.catalogue.subcategories.partials.form', ['subcategory' => $subcategory])<button class="erp-btn-primary">{{ __('Save') }}</button></form></x-admin.card>
</x-admin-layout>
