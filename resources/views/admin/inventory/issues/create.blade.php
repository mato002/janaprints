<x-admin.modal-form
    :title="__('New stock issue')"
    :breadcrumbs="[['label' => __('Stock Issues'), 'url' => route('admin.inventory.issues.index')], ['label' => __('Create')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.inventory.issues.store')">
        @include('admin.inventory.partials.document-header', [
            'type' => 'issue',
            'warehouses' => $warehouses,
            'destinations' => $destinations,
            'formFields' => $formFields,
            'selectedWarehouseId' => $selectedWarehouseId ?? null,
        ])
        @include('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'lineCount' => 5])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save draft') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
