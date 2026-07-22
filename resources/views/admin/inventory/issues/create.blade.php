@php
    $fromStoreDesk = (bool) ($fromStoreDesk ?? request('from') === 'store-desk');
@endphp

<x-admin.modal-form
    :title="__('New stock issue')"
    :breadcrumbs="$fromStoreDesk
        ? [['label' => __('Store Desk'), 'url' => route('admin.store.desk')], ['label' => __('Issue materials')]]
        : [['label' => __('Stock Issues'), 'url' => route('admin.inventory.issues.index')], ['label' => __('Create')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.inventory.issues.store')">
        @if ($fromStoreDesk)
            <input type="hidden" name="from" value="store-desk">
        @endif
        @include('admin.inventory.partials.document-header', [
            'type' => 'issue',
            'warehouses' => $warehouses,
            'destinations' => $destinations,
            'formFields' => $formFields,
            'selectedWarehouseId' => $selectedWarehouseId ?? null,
        ])
        @include('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'lineCount' => 5])
        <x-admin.form-actions>
            <button type="submit" name="intent" value="draft" class="erp-btn-secondary">{{ __('Save draft') }}</button>
            <button type="submit" name="intent" value="post" class="erp-btn-primary">{{ __('Issue & post to stock') }}</button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
