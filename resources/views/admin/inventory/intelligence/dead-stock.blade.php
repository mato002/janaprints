<x-admin-layout :title="__('Dead Stock')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Inventory Intelligence'), 'url' => route('admin.inventory.intelligence.overview')],
    ['label' => __('Dead Stock')],
]">
    <x-admin.page-header :title="__('Dead Stock')" :description="__('Stock with balance and no outbound movement for :days+ days.', ['days' => config('inventory_intelligence.dead_stock_days')])" />

    @include('admin.inventory.intelligence.partials.nav')

    <x-admin.card class="mb-4">
        <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-4">
            <label class="text-xs font-medium text-slate-600">
                {{ __('Warehouse') }}
                <select name="warehouse_id" class="erp-select mt-1 w-full">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? '') == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-medium text-slate-600">
                {{ __('Stock role') }}
                <select name="stock_role" class="erp-select mt-1 w-full">
                    <option value="">{{ __('All') }}</option>
                    @foreach (\App\Enums\InventoryStockRole::cases() as $role)
                        <option value="{{ $role->value }}" @selected(($filters['stock_role'] ?? '') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-medium text-slate-600">
                {{ __('Category') }}
                <select name="category_id" class="erp-select mt-1 w-full">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end">
                <button type="submit" class="erp-btn-secondary">{{ __('Filter') }}</button>
            </div>
        </form>
    </x-admin.card>

    @include('admin.inventory.intelligence.partials.dead-stock-table', [
        'title' => __('Dead stock candidates'),
        'rows' => $rows,
        'empty' => __('No dead stock detected for the selected filters.'),
    ])
</x-admin-layout>
