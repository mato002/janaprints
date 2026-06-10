<x-admin.modal-form
    :title="__('Create department')"
    :breadcrumbs="[['label' => __('Departments'), 'url' => route('admin.departments.index')], ['label' => __('Create')]]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.departments.store')">
        @include('admin.departments.partials.fields', ['department' => null])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
