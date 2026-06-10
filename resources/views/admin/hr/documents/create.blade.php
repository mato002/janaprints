<x-admin.modal-form
    :title="__('Upload document')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Documents'), 'url' => route('admin.hr.documents.dashboard')],
        ['label' => __('Upload')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.documents.store')" enctype="multipart/form-data">
        @include('admin.hr.documents.partials.form')
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Upload') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
