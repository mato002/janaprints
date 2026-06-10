<x-admin.modal-form
    :title="__('Edit role')"
    :breadcrumbs="[
        ['label' => __('Roles'), 'url' => route('admin.access-control.roles')],
        ['label' => $role->name],
    ]"
    maxWidth="lg"
>
    <x-admin.form-shell :action="route('admin.roles.update', $role)" method="PUT">
        <x-admin.input
            name="name"
            :label="__('Role name')"
            :value="old('name', $role->name)"
            :required="true"
            :colSpan="2"
        />

        <x-admin.form-actions>
            <x-primary-button>{{ __('Update') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
