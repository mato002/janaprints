<x-admin.modal-form
    :title="__('Log complaint')"
    :breadcrumbs="[['label' => __('Complaints'), 'url' => route('admin.commercial.complaints.index')], ['label' => __('Create')]]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.commercial.complaints.store')">
        @include('admin.commercial.complaints.form')
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save complaint') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
