@props(['filters', 'branches', 'warehouses', 'categories', 'suppliers'])

<x-admin.card class="mb-6">
    <form method="GET" action="{{ route('admin.procurement.supplier-performance.index') }}" data-turbo-frame="erp-main" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <input type="hidden" name="tab" value="{{ $filters['tab'] ?? 'scorecard' }}">

        <div>
            <label class="text-[11px] text-slate-500" for="from_date">{{ __('From') }}</label>
            <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-input mt-1 w-full">
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="to_date">{{ __('To') }}</label>
            <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-input mt-1 w-full">
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="branch_id">{{ __('Branch') }}</label>
            <select id="branch_id" name="branch_id" class="erp-input mt-1 w-full">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="supplier_id">{{ __('Supplier') }}</label>
            <select id="supplier_id" name="supplier_id" class="erp-input mt-1 w-full">
                <option value="">{{ __('All suppliers') }}</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(($filters['supplier_id'] ?? null) == $supplier->id)>{{ $supplier->vendor_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="warehouse_id">{{ __('Warehouse') }}</label>
            <select id="warehouse_id" name="warehouse_id" class="erp-input mt-1 w-full">
                <option value="">{{ __('All warehouses') }}</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="category_id">{{ __('Category') }}</label>
            <select id="category_id" name="category_id" class="erp-input mt-1 w-full">
                <option value="">{{ __('All categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="text-[11px] text-slate-500" for="search">{{ __('Search') }}</label>
            <input
                type="search"
                id="search"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="{{ __('PO number or supplier…') }}"
                class="erp-input mt-1 w-full"
            >
        </div>
        <div class="flex items-end">
            <button type="submit" class="erp-btn-primary w-full sm:w-auto">{{ __('Apply filters') }}</button>
        </div>
    </form>
</x-admin.card>
