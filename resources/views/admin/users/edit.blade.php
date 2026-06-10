<x-admin.modal-form
    :title="__('Edit user')"
    :breadcrumbs="[['label' => __('Users'), 'url' => route('admin.users.index')], ['label' => $user->name]]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.users.update', $user)" method="PUT">
        @include('admin.users.partials.fields', ['user' => $user])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Update') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
