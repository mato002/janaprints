@php
    $fromStoreDesk = (bool) ($fromStoreDesk ?? request('from') === 'store-desk');
@endphp

<x-admin.modal-form
    :title="__('New stock receipt')"
    :breadcrumbs="$fromStoreDesk
        ? [['label' => __('Store Desk'), 'url' => route('admin.store.desk')], ['label' => __('Receive goods')]]
        : [['label' => __('Receipts'), 'url' => route('admin.inventory.receipts.index')], ['label' => __('Create')]]"
    maxWidth="5xl"
>
    <x-admin.form-shell :action="route('admin.inventory.receipts.store')">
        @if ($fromStoreDesk)
            <input type="hidden" name="from" value="store-desk">
        @endif
        @include('admin.inventory.partials.document-header', ['type' => 'receipt', 'warehouses' => $warehouses, 'sources' => $sources, 'formFields' => $formFields])
        @include('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'dynamic' => true])
        <x-admin.form-actions>
            <button type="submit" name="intent" value="draft" class="erp-btn-secondary">{{ __('Save draft') }}</button>
            <button type="submit" name="intent" value="post" class="erp-btn-primary">{{ __('Receive & post to stock') }}</button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
