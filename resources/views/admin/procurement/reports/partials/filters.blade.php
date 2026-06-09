@props(['filters', 'branches', 'warehouses', 'categories', 'suppliers'])

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="route('admin.procurement.reports.index')" :reset-url="route('admin.procurement.reports.index')" turbo-frame="erp-main">
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'summary' }}">
        <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
        <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
            <option value="">{{ __('All branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        <select id="supplier_id" name="supplier_id" class="erp-toolbar-select" aria-label="{{ __('Supplier') }}">
            <option value="">{{ __('All suppliers') }}</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(($filters['supplier_id'] ?? null) == $supplier->id)>{{ $supplier->vendor_name }}</option>
            @endforeach
        </select>
        <select id="warehouse_id" name="warehouse_id" class="erp-toolbar-select" aria-label="{{ __('Warehouse') }}">
            <option value="">{{ __('All warehouses') }}</option>
            @foreach ($warehouses as $warehouse)
                <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}</option>
            @endforeach
        </select>
        <select id="category_id" name="category_id" class="erp-toolbar-select" aria-label="{{ __('Category') }}">
            <option value="">{{ __('All categories') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        <input
            type="search"
            id="search"
            name="search"
            value="{{ $filters['search'] ?? '' }}"
            placeholder="{{ __('PO number or supplier…') }}"
            class="erp-toolbar-input min-w-[12rem] flex-1"
            data-erp-auto-search
            aria-label="{{ __('Search') }}"
        >
    </x-admin.index-toolbar>
</x-admin.card>
