<x-admin.modal-form
    :title="__('Create branch')"
    :breadcrumbs="[['label' => __('Branches'), 'url' => route('admin.branches.index')], ['label' => __('Create')]]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.branches.store')">
        @include('admin.branches.partials.fields', ['branch' => null])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
