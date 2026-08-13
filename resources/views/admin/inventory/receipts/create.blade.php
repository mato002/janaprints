@php
    $fromStoreDesk = (bool) ($fromStoreDesk ?? request('from') === 'store-desk');
    $sourceJobCard = $sourceJobCard ?? null;
    $prefilledLines = $prefilledLines ?? [];
    $prefilledNotes = $prefilledNotes ?? null;
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
        @if ($sourceJobCard)
            <input type="hidden" name="job_card_id" value="{{ $sourceJobCard->getRouteKey() }}">
            <input type="hidden" name="notes" value="{{ old('notes', $prefilledNotes) }}">
        @endif
        @if ($sourceJobCard && $prefilledLines !== [])
            <p class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-950">
                {{ __('Lines are filled from shortages on :job. Change quantity or unit cost if this receipt is different, then receive.', ['job' => $sourceJobCard->job_card_number]) }}
            </p>
        @endif
        @include('admin.inventory.partials.document-header', [
            'type' => 'receipt',
            'warehouses' => $warehouses,
            'sources' => $sources,
            'formFields' => $formFields,
            'selectedWarehouseId' => $selectedWarehouseId ?? null,
        ])
        @include('admin.inventory.partials.line-items', [
            'items' => $items,
            'formFields' => $formFields,
            'dynamic' => true,
            'prefilledLines' => $prefilledLines,
        ])
        <x-admin.form-actions>
            <button type="submit" name="intent" value="draft" class="erp-btn-secondary">{{ __('Save draft') }}</button>
            <button type="submit" name="intent" value="post" class="erp-btn-primary">{{ __('Receive & post to stock') }}</button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
