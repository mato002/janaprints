<div class="erp-form-grid">
    <x-admin.lookup-select
        name="warehouse_id"
        :label="__('Warehouse')"
        :options="$warehouses"
        :value="old('warehouse_id')"
        :required="true"
        refresh-route="admin.lookups.warehouses"
        create-route="admin.inventory.warehouses.quick-create"
        permission="inventory.create"
        :modal-title="__('Create warehouse')"
        option-label-key="name"
        select-class="erp-select mt-1"
        :empty-option="false"
    />

    <x-admin.lookup-select
        name="inventory_item_id"
        :label="__('Inventory item')"
        :options="$items"
        :value="old('inventory_item_id')"
        :required="true"
        refresh-route="admin.lookups.items"
        create-route="admin.inventory.items.quick-create"
        permission="catalogue.create"
        :modal-title="__('Create item')"
        select-class="erp-select mt-1"
        :empty-option="false"
    />

    <x-admin.form-field name="min_level" :label="__('Min level')" :required="true">
        <input
            type="number"
            step="0.001"
            min="0"
            name="min_level"
            class="erp-input w-full"
            value="{{ old('min_level') }}"
            required
        >
    </x-admin.form-field>

    <x-admin.form-field name="max_level" :label="__('Max level')">
        <input
            type="number"
            step="0.001"
            min="0"
            name="max_level"
            class="erp-input w-full"
            value="{{ old('max_level') }}"
        >
    </x-admin.form-field>

    <x-admin.form-field name="reorder_quantity" :label="__('Reorder quantity')" :required="true">
        <input
            type="number"
            step="0.001"
            min="0"
            name="reorder_quantity"
            class="erp-input w-full"
            value="{{ old('reorder_quantity') }}"
            required
        >
    </x-admin.form-field>

    <x-admin.form-field name="safety_stock" :label="__('Safety stock')" :required="true">
        <input
            type="number"
            step="0.001"
            min="0"
            name="safety_stock"
            class="erp-input w-full"
            value="{{ old('safety_stock') }}"
            required
        >
    </x-admin.form-field>
</div>

<p class="mt-4 text-xs text-slate-500">
    {{ __('Saving a rule for an existing warehouse and item pair updates the configuration.') }}
</p>
