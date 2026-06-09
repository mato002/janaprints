@props(['filters', 'branches', 'warehouses' => null, 'vendors' => null, 'showKpiCategory' => false, 'can_export' => false, 'export_route' => null, 'export_query' => null, 'export_route_params' => [], 'format_in_path' => false])

<x-admin.card :padding="false" class="mb-4">
    <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()" turbo-frame="erp-main">
        @if ($can_export && filled($export_route))
            <x-slot name="export">
                @include('admin.reports.partials.export-button', [
                    'can_export' => $can_export,
                    'export_route' => $export_route,
                    'export_query' => $export_query ?? request()->query(),
                    'export_route_params' => $export_route_params,
                    'format_in_path' => $format_in_path,
                ])
            </x-slot>
        @endif

        <input type="date" id="from_date" name="from_date" value="{{ $filters['from_date'] }}" class="erp-toolbar-input" aria-label="{{ __('From date') }}">
        <input type="date" id="to_date" name="to_date" value="{{ $filters['to_date'] }}" class="erp-toolbar-input" aria-label="{{ __('To date') }}">
        <select id="branch_id" name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
            <option value="">{{ __('All branches') }}</option>
            @foreach ($branches as $branch)
                <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? null) == $branch->id)>{{ $branch->name }}</option>
            @endforeach
        </select>
        @if ($warehouses)
            <select id="warehouse_id" name="warehouse_id" class="erp-toolbar-select" aria-label="{{ __('Warehouse') }}">
                <option value="">{{ __('All warehouses') }}</option>
                @foreach ($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? null) == $warehouse->id)>{{ $warehouse->name }}</option>
                @endforeach
            </select>
        @endif
        @if ($vendors)
            <select id="vendor_id" name="vendor_id" class="erp-toolbar-select" aria-label="{{ __('Vendor') }}">
                <option value="">{{ __('All vendors') }}</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected(($filters['vendor_id'] ?? null) == $vendor->id)>{{ $vendor->vendor_name ?? $vendor->name }}</option>
                @endforeach
            </select>
        @endif
        @if ($showKpiCategory)
            <select id="kpi_category" name="kpi_category" class="erp-toolbar-select" aria-label="{{ __('KPI category') }}">
                <option value="">{{ __('All categories') }}</option>
                @foreach (['commercial', 'production', 'inventory', 'procurement', 'accounting', 'hr'] as $cat)
                    <option value="{{ $cat }}" @selected(($filters['kpi_category'] ?? '') === $cat)>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
        @endif
    </x-admin.index-toolbar>
</x-admin.card>
