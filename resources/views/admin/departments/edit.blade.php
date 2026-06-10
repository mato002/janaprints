<x-admin.modal-form
    :title="__('Edit department')"
    :breadcrumbs="[['label' => __('Departments'), 'url' => route('admin.departments.index')], ['label' => $department->name]]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.departments.update', $department)" method="PUT">
        @include('admin.departments.partials.fields', ['department' => $department])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
