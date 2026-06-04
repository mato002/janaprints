<x-admin-layout :title="__('New Brand')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Brands'), 'url' => route('admin.inventory.catalogue.brands.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New Brand')" />
    <x-admin.card><form method="POST" enctype="multipart/form-data" action="{{ route('admin.inventory.catalogue.brands.store') }}" class="space-y-4 max-w-3xl">@csrf @include('admin.inventory.catalogue.brands.partials.form')<button class="erp-btn-primary">{{ __('Save') }}</button></form></x-admin.card>
</x-admin-layout>
