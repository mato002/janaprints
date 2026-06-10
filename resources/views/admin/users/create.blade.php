<x-admin.modal-form
    :title="__('Create user')"
    :breadcrumbs="[['label' => __('Users'), 'url' => route('admin.users.index')], ['label' => __('Create')]]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.users.store')">
        @include('admin.users.partials.fields', ['user' => null])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Create') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
