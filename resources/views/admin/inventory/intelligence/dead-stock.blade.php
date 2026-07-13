<x-admin-layout :title="__('Dead Stock')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Inventory Intelligence'), 'url' => route('admin.inventory.intelligence.overview')],
    ['label' => __('Dead Stock')],
]">
    <x-admin.page-header :title="__('Dead Stock')" :description="__('Stock with balance and no outbound movement for :days+ days.', ['days' => config('inventory_intelligence.dead_stock_days')])" />

    @include('admin.inventory.intelligence.partials.nav')

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <select name="warehouse_id" class="erp-toolbar-select" aria-label="{{ __('Warehouse') }}">
                <option value="">{{ __('All warehouses') }}</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? '') == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
            <select name="stock_role" class="erp-toolbar-select" aria-label="{{ __('Stock role') }}">
                <option value="">{{ __('All stock roles') }}</option>
                @foreach (\App\Enums\InventoryStockRole::cases() as $role)
                    <option value="{{ $role->value }}" @selected(($filters['stock_role'] ?? '') === $role->value)>{{ $role->label() }}</option>
                @endforeach
            </select>
            <select name="category_id" class="erp-toolbar-select" aria-label="{{ __('Category') }}">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    @include('admin.inventory.intelligence.partials.dead-stock-table', [
        'title' => __('Dead stock candidates'),
        'rows' => $rows,
        'empty' => __('No dead stock detected for the selected filters.'),
    ])
</x-admin-layout>
