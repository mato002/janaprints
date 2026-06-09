<x-admin.modal-form
    :title="__('Create customer')"
    :breadcrumbs="[['label' => __('Customers'), 'url' => route('admin.crm.customers.index')], ['label' => __('Create')]]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.crm.customers.store')">
        @include('admin.crm.customers.form', ['customer' => null])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
