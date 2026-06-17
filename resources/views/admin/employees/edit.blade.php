<x-admin.modal-form
    :title="__('Edit employee')"
    :breadcrumbs="[['label' => __('Employees'), 'url' => route('admin.employees.index')], ['label' => __('Edit')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.employees.update', $employee)" method="PUT">
        @include('admin.employees.partials.form-fields', ['employee' => $employee])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
