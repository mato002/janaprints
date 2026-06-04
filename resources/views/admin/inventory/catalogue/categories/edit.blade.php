<x-admin-layout :title="__('Edit Category')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Categories'), 'url' => route('admin.inventory.catalogue.categories.index')], ['label' => __('Edit')]]">
    <x-admin.page-header :title="$category->name" />
    <x-admin.card><form method="POST" action="{{ route('admin.inventory.catalogue.categories.update', $category) }}" class="space-y-4 max-w-3xl">@csrf @method('PUT') @include('admin.inventory.catalogue.categories.partials.form', ['category' => $category])<button class="erp-btn-primary">{{ __('Save') }}</button></form></x-admin.card>
</x-admin-layout>
