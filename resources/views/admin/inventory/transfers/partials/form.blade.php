@php($fields = $formFields ?? [])

<div class="erp-form-grid">
    @if (($fields['warehouse_id']['visible'] ?? true))
        <x-admin.lookup-select
            name="warehouse_id"
            :label="$fields['warehouse_id']['label'] ?? __('From store')"
            :options="$warehouses"
            :value="old('warehouse_id')"
            :required="($fields['warehouse_id']['required'] ?? true)"
            create-route="admin.inventory.warehouses.quick-create"
            refresh-route="admin.lookups.warehouses"
            permission="inventory.create"
            :modal-title="__('Create warehouse')"
            option-label-key="name"
            select-class="erp-select mt-1"
            :empty-option="false"
        />
    @endif
    @if (($fields['to_warehouse_id']['visible'] ?? true))
        <x-admin.lookup-select
            name="to_warehouse_id"
            :label="$fields['to_warehouse_id']['label'] ?? __('To store')"
            :options="$warehouses"
            :value="old('to_warehouse_id')"
            :required="($fields['to_warehouse_id']['required'] ?? true)"
            create-route="admin.inventory.warehouses.quick-create"
            refresh-route="admin.lookups.warehouses"
            permission="inventory.create"
            :modal-title="__('Create warehouse')"
            option-label-key="name"
            select-class="erp-select mt-1"
            :empty-option="false"
        />
    @endif
    @if (($fields['issue_date']['visible'] ?? true))
        <div>
            <x-input-label for="issue_date" :value="$fields['issue_date']['label'] ?? __('Transfer date')" />
            <x-text-input id="issue_date" name="issue_date" type="date" class="block mt-1 w-full" :value="old('issue_date', now()->toDateString())" @required($fields['issue_date']['required'] ?? true) />
        </div>
    @endif
    @if (($fields['notes']['visible'] ?? true))
        <div class="md:col-span-2">
            <x-input-label for="notes" :value="$fields['notes']['label'] ?? __('Notes')" />
            <textarea id="notes" name="notes" class="erp-input mt-1 w-full" rows="2" @required($fields['notes']['required'] ?? false)>{{ old('notes') }}</textarea>
        </div>
    @endif
</div>

@include('admin.inventory.partials.line-items', ['items' => $items, 'formFields' => $formFields, 'dynamic' => true])
