@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Variance Report')],
    ];
@endphp
<x-admin-layout :title="__('Variance Report')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('Variance report')" />

    <x-admin.data-table
        :search-placeholder="__('Search variances…')"
        :filterable="true"
        export-filename="inventory-variances"
        export-route="admin.inventory.variances.export"
        :export-query="request()->query()"
        :format-in-path="true"
    >
        <x-slot name="filters">
            <form method="GET" x-data="erpIndexFilterForm()" @change="onFieldChange($event)" class="mb-4 flex flex-wrap items-center gap-2">
                <select name="warehouse_id" class="erp-toolbar-select" aria-label="{{ __('Warehouse') }}">
                    <option value="">{{ __('All warehouses') }}</option>
                    @foreach ($warehouses as $w)
                        <option value="{{ $w->id }}" @selected(($filters['warehouse_id'] ?? '') == $w->id)>{{ $w->name }}</option>
                    @endforeach
                </select>
                <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->value }}</option>
                    @endforeach
                </select>
                <select name="variance_type" class="erp-toolbar-select" aria-label="{{ __('Variance') }}">
                    <option value="">{{ __('All variance types') }}</option>
                    <option value="positive" @selected(($filters['variance_type'] ?? '') === 'positive')>{{ __('Positive') }}</option>
                    <option value="negative" @selected(($filters['variance_type'] ?? '') === 'negative')>{{ __('Negative') }}</option>
                </select>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('From') }}">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('To') }}">
                <a href="{{ route('admin.inventory.variances.index') }}" class="erp-btn-ghost py-1 text-xs text-slate-500" data-turbo-frame="erp-main">{{ __('Reset') }}</a>
            </form>
        </x-slot>
        <x-slot name="head">
            <tr>
                <th>{{ __('Count') }}</th>
                <th>{{ __('Warehouse') }}</th>
                <th>{{ __('Item') }}</th>
                <th>{{ __('System') }}</th>
                <th>{{ __('Counted') }}</th>
                <th>{{ __('Variance') }}</th>
                <th>{{ __('Value') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($variances as $line)
                <tr x-show="rowVisible(@js(strtolower($line->stockCount?->count_number.' '.$line->inventoryItem?->item_name)))">
                    <td>{{ $line->stockCount?->count_number }}</td>
                    <td>{{ $line->stockCount?->warehouse?->name }}</td>
                    <td>{{ $line->inventoryItem?->item_name }}</td>
                    <td>{{ $line->system_quantity }}</td>
                    <td>{{ $line->counted_quantity }}</td>
                    <td>{{ $line->variance_quantity }}</td>
                    <td>{{ number_format((float) $line->variance_value, 2) }}</td>
                    <td><x-admin.enum-status-badge :status="$line->stockCount?->status?->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.inventory.stock-counts.show', $line->stockCount)">{{ __('View Count') }}</x-admin.table-row-action>
                            <x-admin.table-row-action :href="route('admin.inventory.variances.export', ['warehouse_id' => $line->stockCount?->warehouse_id])" data-turbo="false">{{ __('Export') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9"><x-admin.empty-state icon="chart-bar" :title="__('No variances found')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$variances" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
