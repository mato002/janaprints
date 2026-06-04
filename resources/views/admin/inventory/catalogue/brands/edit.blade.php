<x-admin-layout :title="__('Edit Brand')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Brands'), 'url' => route('admin.inventory.catalogue.brands.index')], ['label' => __('Edit')]]">
    <x-admin.page-header :title="$brand->name" />
    <x-admin.card><form method="POST" enctype="multipart/form-data" action="{{ route('admin.inventory.catalogue.brands.update', $brand) }}" class="space-y-4 max-w-3xl">@csrf @method('PUT') @include('admin.inventory.catalogue.brands.partials.form', ['brand' => $brand])<button class="erp-btn-primary">{{ __('Save') }}</button></form></x-admin.card>
</x-admin-layout>
