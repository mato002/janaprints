@php
    $spec = $specification ?? null;
    $isEdit = (bool) $spec;
    $prefill = fn (string $field, mixed $default = null) => old(
        $field,
        $spec?->{$field}
            ?? ($templateDefaults[$field] ?? null)
            ?? $default
    );
@endphp

<x-admin-layout
    :title="$isEdit ? __('Edit production specification') : __('Production specification')"
    :breadcrumbs="[
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
        ['label' => $salesOrder->order_number, 'url' => route('admin.sales-orders.show', $salesOrder)],
        ['label' => $isEdit ? __('Edit specification') : __('Add specification')],
    ]"
>
    <x-admin.page-header
        :title="$isEdit ? __('Edit production specification') : __('Production specification')"
        :description="$salesOrderItem->item_name . ' × ' . $salesOrderItem->quantity"
    />

    <form
        method="POST"
        action="{{ $isEdit
            ? route('admin.sales-orders.items.specification.update', [$salesOrder, $salesOrderItem, $spec])
            : route('admin.sales-orders.items.specification.store', [$salesOrder, $salesOrderItem]) }}"
        class="space-y-6"
    >
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        @include('admin.production.specifications.partials.form-fields', [
            'specification' => $spec,
            'templateDefaults' => $templateDefaults ?? [],
            'prefill' => $prefill,
            'printTemplates' => $printTemplates ?? collect(),
            'selectedTemplateId' => $selectedTemplateId ?? null,
            'productionTypes' => $productionTypes,
            'inkTypes' => $inkTypes,
            'approvalStatuses' => $approvalStatuses,
            'paperItems' => $paperItems,
            'materialItems' => $materialItems,
            'inkProfiles' => $inkProfiles,
        ])

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="erp-btn-primary">{{ __('Save specification') }}</button>
            <a href="{{ route('admin.sales-orders.show', $salesOrder) }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
</x-admin-layout>
