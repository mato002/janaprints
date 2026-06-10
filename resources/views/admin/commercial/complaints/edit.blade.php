<x-admin.modal-form
    :title="__('Edit complaint')"
    :breadcrumbs="[['label' => __('Complaints'), 'url' => route('admin.commercial.complaints.index')], ['label' => $complaint->subject]]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.commercial.complaints.update', $complaint)" method="PUT">
        @include('admin.commercial.complaints.form', ['complaint' => $complaint])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save changes') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
