<x-admin.modal-form
    :title="__('New stock receipt')"
    :breadcrumbs="[['label' => __('Receipts'), 'url' => route('admin.inventory.receipts.index')], ['label' => __('Create')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.inventory.receipts.store')">
        @include('admin.inventory.partials.document-header', ['type' => 'receipt', 'warehouses' => $warehouses, 'sources' => $sources, 'formFields' => $formFields])
        @include('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'dynamic' => true])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save draft') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
