@php($fromStoreDesk = (bool) ($fromStoreDesk ?? request('from') === 'store-desk'))
<x-admin.modal-form
    :title="__('New adjustment')"
    :breadcrumbs="$fromStoreDesk
        ? [['label' => __('Store Desk'), 'url' => route('admin.store.desk')], ['label' => __('Adjust stock')]]
        : [['label' => __('Adjustments'), 'url' => route('admin.inventory.adjustments.index')], ['label' => __('Create')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.inventory.adjustments.store')">
        @if ($fromStoreDesk)
            <input type="hidden" name="from" value="store-desk">
        @endif
        @include('admin.inventory.partials.document-header', ['type' => 'adjustment', 'warehouses' => $warehouses, 'formFields' => $formFields])
        @include('admin.inventory.partials.line-items', ['items' => $items, 'directions' => $directions, 'formFields' => $formFields, 'lineCount' => 5])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save draft') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
