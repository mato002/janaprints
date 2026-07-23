<x-admin.modal-form
    :title="__('Create print specification')"
    :breadcrumbs="[
        ['label' => __('Customers'), 'url' => route('admin.crm.customers.index')],
        ['label' => $customer->company_name, 'url' => route('admin.crm.customers.show', $customer)],
        ['label' => __('Create print specification')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.crm.customers.print-specifications.store', $customer)" enctype="multipart/form-data">
        @if (request('from') === 'sales-desk')
            <input type="hidden" name="from" value="sales-desk">
        @elseif (request('from') === 'sales-order')
            <input type="hidden" name="from" value="sales-order">
        @endif
        @include('admin.crm.customers.print-specifications.partials.form', [
            'customer' => $customer,
            'specification' => null,
            'serialProfile' => null,
            'serialSummary' => null,
            'statuses' => $statuses,
            'billingTypes' => $billingTypes,
            'fulfilmentMethods' => $fulfilmentMethods,
            'artworkTypes' => $artworkTypes,
            'defaultStatus' => in_array(request('from'), ['sales-desk', 'sales-order'], true) ? 'active' : 'draft',
        ])
        <x-admin.form-modal-actions class="erp-form-modal__actions--sticky">
            <x-primary-button class="min-h-[2.75rem]">{{ __('Save specification') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
