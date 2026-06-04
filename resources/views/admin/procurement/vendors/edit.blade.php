<x-admin-layout :title="__('Edit Vendor')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Vendor Management')], ['label' => __('Vendors'), 'url' => route('admin.procurement.vendors.index')], ['label' => $vendor->vendor_name]]">
    <x-admin.page-header :title="__('Edit vendor')" />
    <x-admin.card>
        <form method="POST" action="{{ route('admin.procurement.vendors.update', $vendor) }}" class="erp-form-grid">
            @csrf @method('PUT')
            @include('admin.procurement.vendors.partials.form', ['vendor' => $vendor])
            <div class="md:col-span-2"><x-primary-button>{{ __('Save changes') }}</x-primary-button></div>
        </form>
    </x-admin.card>
</x-admin-layout>
