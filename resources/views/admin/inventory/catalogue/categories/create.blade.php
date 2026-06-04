<x-admin-layout :title="__('New Category')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Categories'), 'url' => route('admin.inventory.catalogue.categories.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('New Category')" />
    <x-admin.card><form method="POST" action="{{ route('admin.inventory.catalogue.categories.store') }}" class="space-y-4 max-w-3xl">@csrf @include('admin.inventory.catalogue.categories.partials.form')<button class="erp-btn-primary">{{ __('Save') }}</button></form></x-admin.card>
</x-admin-layout>
