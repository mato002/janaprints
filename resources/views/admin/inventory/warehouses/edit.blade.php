<x-admin.modal-form
    :title="__('Edit warehouse')"
    :breadcrumbs="[['label' => __('Warehouses'), 'url' => route('admin.inventory.warehouses.index')], ['label' => __('Edit')]]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.inventory.warehouses.update', $warehouse)" method="PUT">
        @include('admin.inventory.warehouses.form')
        <x-admin.form-actions>
            <x-primary-button>{{ __('Update') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
