<x-admin.modal-form
    :title="__('New adjustment')"
    :breadcrumbs="[['label' => __('Adjustments'), 'url' => route('admin.inventory.adjustments.index')], ['label' => __('Create')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.inventory.adjustments.store')">
        @include('admin.inventory.partials.document-header', ['type' => 'adjustment', 'warehouses' => $warehouses, 'formFields' => $formFields])
        @include('admin.inventory.partials.line-items', ['items' => $items, 'directions' => $directions, 'formFields' => $formFields, 'lineCount' => 5])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save draft') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
