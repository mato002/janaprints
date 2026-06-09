@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Store Operations'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'store-operations'])],
        ['label' => __('Reorder Alerts')],
    ];
@endphp
<x-admin-layout :title="__('Reorder Alerts')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('Reorder Alerts')" :description="__('Actionable low-stock alerts with acknowledgement, resolution, and purchase request handoff.')" />

    <x-admin.data-table :search-placeholder="__('Search SKU or item name…')" export-filename="reorder-alerts">
        <x-slot name="filters">
            <form method="GET" action="{{ route('admin.inventory.alerts.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Warehouse') }}
                    <select name="warehouse_id" class="erp-select mt-1 w-full">
                        <option value="">{{ __('All warehouses') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" @selected(($filters['warehouse_id'] ?? '') == $warehouse->id)>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Category') }}
                    <select name="category_id" class="erp-select mt-1 w-full">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Status') }}
                    <select name="status" class="erp-select mt-1 w-full">
                        <option value="all">{{ __('All statuses') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? 'all') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex flex-col justify-end gap-2">
                    <label class="inline-flex items-center gap-2 text-xs font-medium text-slate-600">
                        <input type="checkbox" name="critical_only" value="1" class="rounded border-slate-300" @checked(! empty($filters['critical_only']))>
                        {{ __('Critical only') }}
                    </label>
                    <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
                    <button type="submit" class="erp-btn-secondary">{{ __('Apply filters') }}</button>
                </div>
            </form>
        </x-slot>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Item') }}</th>
                <th scope="col">{{ __('SKU') }}</th>
                <th scope="col">{{ __('Warehouse') }}</th>
                <th scope="col">{{ __('Current Qty') }}</th>
                <th scope="col">{{ __('Reorder Level') }}</th>
                <th scope="col">{{ __('Shortage Qty') }}</th>
                <th scope="col">{{ __('Replenishment') }}</th>
                <th scope="col">{{ __('Alert Age') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($alerts as $alert)
                <tr x-show="rowVisible(@js(strtolower(($alert->inventoryItem?->sku ?? '').' '.($alert->inventoryItem?->item_name ?? '').' '.($alert->warehouse?->name ?? '').' '.$alert->status->value)))">
                    <td class="font-medium">{{ $alert->inventoryItem?->item_name }}</td>
                    <td class="font-mono text-xs">{{ $alert->inventoryItem?->sku }}</td>
                    <td>{{ $alert->warehouse?->name ?? '—' }}</td>
                    <td class="tabular-nums">{{ number_format((float) $alert->current_quantity, 3) }}</td>
                    <td class="tabular-nums">{{ number_format((float) $alert->reorder_level, 3) }}</td>
                    <td class="tabular-nums">{{ number_format($alert->shortageQuantity(), 3) }}</td>
                    <td>
                        @if ($alert->replenishment_action)
                            <span class="font-medium">{{ $alert->replenishment_action->label() }}</span>
                            @if ($alert->sourceWarehouse)
                                <span class="block text-xs text-slate-500">{{ __('From') }} {{ $alert->sourceWarehouse->name }}</span>
                            @endif
                            <span class="block text-xs tabular-nums text-slate-500">{{ number_format((float) $alert->recommended_quantity, 3) }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ trans_choice(':count day|:count days', $alert->alertAgeDays(), ['count' => $alert->alertAgeDays()]) }}</td>
                    <td><x-admin.enum-status-badge :status="$alert->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @if ($alert->inventoryItem)
                                <x-admin.table-row-action :href="route('admin.inventory.items.show', $alert->inventoryItem)">{{ __('View Item') }}</x-admin.table-row-action>
                            @endif
                            @if ($alert->warehouse)
                                <x-admin.table-row-action :href="route('admin.inventory.warehouses.show', $alert->warehouse)">{{ __('View Warehouse') }}</x-admin.table-row-action>
                            @endif
                            @can('createPurchaseRequest', $alert)
                                <x-admin.table-row-action method="POST" :action="route('admin.inventory.alerts.purchase-request', $alert)">{{ __('Create Purchase Request') }}</x-admin.table-row-action>
                            @endcan
                            @can('acknowledge', $alert)
                                <x-admin.table-row-action method="POST" :action="route('admin.inventory.alerts.acknowledge', $alert)">{{ __('Acknowledge') }}</x-admin.table-row-action>
                            @endcan
                            @can('resolve', $alert)
                                <x-admin.table-row-action method="POST" :action="route('admin.inventory.alerts.resolve', $alert)">{{ __('Resolve') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10"><x-admin.empty-state icon="bell" :title="__('No reorder alerts')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$alerts" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
