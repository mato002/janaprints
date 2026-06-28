<x-admin.modal-form
    :title="__('Create vendor')"
    :breadcrumbs="[['label' => __('Vendors'), 'url' => route('admin.procurement.vendors.index')], ['label' => __('Create')]]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.procurement.vendors.store')">
        @include('admin.procurement.vendors.partials.form')
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save vendor') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
