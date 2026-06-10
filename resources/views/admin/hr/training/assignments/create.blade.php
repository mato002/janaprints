<x-admin.modal-form
    :title="__('Assign training')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')],
        ['label' => __('Assign')],
    ]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.hr.training.assignments.store')">
        @include('admin.hr.training.assignments.partials.form')
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Assign') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
