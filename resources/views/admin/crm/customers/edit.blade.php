<x-admin.modal-form
    :title="__('Edit customer')"
    :breadcrumbs="[['label' => __('Customers'), 'url' => route('admin.crm.customers.index')], ['label' => __('Edit')]]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.crm.customers.update', $customer)" method="PUT">
        @if (request('from') === 'sales-desk')
            <input type="hidden" name="from" value="sales-desk">
        @endif
        @include('admin.crm.customers.form', ['customer' => $customer])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Update') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
