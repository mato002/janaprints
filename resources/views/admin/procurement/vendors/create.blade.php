<x-admin-layout :title="__('Create Vendor')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Vendor Management')], ['label' => __('Vendors'), 'url' => route('admin.procurement.vendors.index')], ['label' => __('Create')]]">
    <x-admin.page-header :title="__('Create vendor')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.procurement.vendors.store') }}" class="erp-form-grid">
            @csrf
            @include('admin.procurement.vendors.partials.form')
            <div class="md:col-span-2"><x-primary-button>{{ __('Save vendor') }}</x-primary-button></div>
        </form>
    </x-admin.card>
</x-admin-layout>
