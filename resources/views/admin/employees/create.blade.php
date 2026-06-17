<x-admin.modal-form
    :title="__('Create employee')"
    :breadcrumbs="[['label' => __('Employees'), 'url' => route('admin.employees.index')], ['label' => __('Create')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.employees.store')">
        @include('admin.employees.partials.form-fields', ['employee' => null])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
