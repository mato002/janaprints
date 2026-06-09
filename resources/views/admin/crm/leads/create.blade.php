<x-admin.modal-form
    :title="__('Create lead')"
    :breadcrumbs="[['label' => __('Leads'), 'url' => route('admin.crm.leads.index')], ['label' => __('Create')]]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.crm.leads.store')">
        @include('admin.crm.leads.form', ['lead' => null])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
