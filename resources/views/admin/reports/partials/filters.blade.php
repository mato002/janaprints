@props(['filters', 'branches', 'warehouses' => null, 'vendors' => null, 'showKpiCategory' => false])

<x-admin.card class="mb-6">
    <form method="GET" action="{{ url()->current() }}" data-turbo-frame="erp-main" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="text-[11px] text-slate-500" for="from_date">{{ __('From') }}</label>
            <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-input mt-1">
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="to_date">{{ __('To') }}</label>
            <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-input mt-1">
        </div>
        <div>
            <label class="text-[11px] text-slate-500" for="branch_id">{{ __('Branch') }}</label>
            <select id="branch_id" name="branch_id" class="erp-input mt-1 min-w-[10rem]">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @if ($warehouses)
            <div>
                <label class="text-[11px] text-slate-500" for="warehouse_id">{{ __('Warehouse') }}</label>
                <select id="warehouse_id" name="warehouse_id" class="erp-input mt-1 min-w-[10rem]">
                    <option value="">{{ __('All warehouses') }}</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if ($vendors)
            <div>
                <label class="text-[11px] text-slate-500" for="vendor_id">{{ __('Vendor') }}</label>
                <select id="vendor_id" name="vendor_id" class="erp-input mt-1 min-w-[10rem]">
                    <option value="">{{ __('All vendors') }}</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->id }}" @selected(($filters['vendor_id'] ?? null) == $vendor->id)>{{ $vendor->vendor_name ?? $vendor->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if ($showKpiCategory)
            <div>
                <label class="text-[11px] text-slate-500" for="kpi_category">{{ __('KPI category') }}</label>
                <select id="kpi_category" name="kpi_category" class="erp-input mt-1 min-w-[10rem]">
                    <option value="">{{ __('All categories') }}</option>
                    @foreach (['commercial', 'production', 'inventory', 'procurement', 'accounting', 'hr'] as $cat)
                        <option value="{{ $cat }}" @selected(($filters['kpi_category'] ?? '') === $cat)>{{ ucfirst($cat) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <button type="submit" class="erp-btn-primary">{{ __('Apply filters') }}</button>
    </form>
</x-admin.card>
