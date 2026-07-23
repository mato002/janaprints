@props([
    'scopedCustomerId' => null,
    'value' => null,
])

@if ($scopedCustomerId)
    <input type="hidden" name="customer_id" value="{{ $scopedCustomerId }}">
@endif

<x-admin.lookup-select
    name="customer_artwork_id"
    :label="__('Artwork')"
    :options="[]"
    :value="old('customer_artwork_id', $value)"
    create-route="admin.crm.customer-artworks.quick-create"
    refresh-route="admin.lookups.customer_artworks"
    permission="crm.customers.edit"
    :modal-title="__('Create artwork')"
    scope-customer-field="customer_id"
    select-class="erp-input w-full"
    :placeholder="__('None')"
/>
