<x-admin.modal-form
    :title="__('Edit vendor')"
    :breadcrumbs="[['label' => __('Vendors'), 'url' => route('admin.procurement.vendors.index')], ['label' => $vendor->vendor_name]]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.procurement.vendors.update', $vendor)" method="PUT">
        @include('admin.procurement.vendors.partials.form', ['vendor' => $vendor])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save changes') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
