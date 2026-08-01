@php($m = $subcategory ?? null)
@php($defaultCategoryId = $defaultCategoryId ?? null)
<div class="erp-form-grid">
    <x-admin.lookup-select
        name="inventory_category_id"
        :label="__('Category')"
        :options="$categories"
        :value="old('inventory_category_id', $m?->inventory_category_id ?? $defaultCategoryId)"
        :required="true"
        create-route="admin.inventory.catalogue.categories.quick-create"
        refresh-route="admin.lookups.categories"
        permission="catalogue.create"
        :modal-title="__('Create category')"
        option-label-key="name"
        option-value-key="id"
        select-class="erp-select w-full"
        :empty-option="false"
    />
    <div><label class="erp-label">{{ __('Name') }}</label><input name="name" class="erp-input w-full" value="{{ old('name', $m?->name) }}" required></div>
    <x-admin.entity-code-input :record="$m" erp />
    <div class="md:col-span-2"><label class="erp-label">{{ __('Description') }}</label><textarea name="description" class="erp-input w-full" rows="3">{{ old('description', $m?->description) }}</textarea></div>
    <div><input type="hidden" name="is_active" value="0"><label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $m?->is_active ?? true))><span>{{ __('Active') }}</span></label></div>
</div>
