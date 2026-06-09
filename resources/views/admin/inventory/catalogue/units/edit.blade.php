<x-admin-layout :title="__('Edit Unit of Measure')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Units of Measure'), 'url' => route('admin.inventory.catalogue.units.index')], ['label' => $unit->name]]">
    <x-admin.page-header :title="$unit->name" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.inventory.catalogue.units.update', $unit) }}" class="space-y-4 max-w-3xl">
            @csrf
            @method('PUT')
            @include('admin.inventory.catalogue.units.partials.form', ['unit' => $unit])
            <button class="erp-btn-primary">{{ __('Save changes') }}</button>
        </form>
    </x-admin.card>
</x-admin-layout>
