<x-admin.modal-form
    :title="__('New Requisition')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Recruitment'), 'url' => route('admin.hr.recruitment.dashboard')],
        ['label' => __('Requisitions'), 'url' => route('admin.hr.recruitment.dashboard', ['tab' => 'requisitions'])],
        ['label' => __('New')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.recruitment.requisitions.store')">
        @include('admin.hr.recruitment.partials.requisition-form', ['formData' => $formData])

        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Create requisition') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
