<x-admin.modal-form
    :title="__('Edit lead')"
    :breadcrumbs="[['label' => __('Leads'), 'url' => route('admin.crm.leads.index')], ['label' => __('Edit')]]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.crm.leads.update', $lead)" method="PUT">
        @include('admin.crm.leads.form', ['lead' => $lead])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Update') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
