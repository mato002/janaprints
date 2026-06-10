<x-admin.modal-form
    :title="__('Create warehouse')"
    :breadcrumbs="[['label' => __('Warehouses'), 'url' => route('admin.inventory.warehouses.index')], ['label' => __('Create')]]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.inventory.warehouses.store')">
        @include('admin.inventory.warehouses.form', ['warehouse' => null])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Create') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
