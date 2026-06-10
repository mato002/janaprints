<x-admin.modal-form
    :title="__('Edit branch')"
    :breadcrumbs="[['label' => __('Branches'), 'url' => route('admin.branches.index')], ['label' => $branch->name]]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.branches.update', $branch)" method="PUT">
        @include('admin.branches.partials.fields', ['branch' => $branch])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
