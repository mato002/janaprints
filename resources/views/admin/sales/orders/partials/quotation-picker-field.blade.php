@props([
    'value' => null,
    'required' => true,
])

<x-admin.lookup-select
    name="quotation_id"
    :label="__('Quotation')"
    :options="[]"
    :value="old('quotation_id', $value)"
    :required="$required"
    create-route="admin.quotations.quick-create"
    refresh-route="admin.lookups.sales_order_quotations"
    permission="quotations.create"
    :modal-title="__('Create quotation')"
    select-class="erp-select w-full min-h-[2.75rem]"
    :placeholder="__('Select quotation')"
/>

<p class="mt-2 text-xs text-slate-500">
    {{ __('Only accepted quotations with approved artwork appear here. If none are listed, accept the quotation and approve artwork first.') }}
</p>
