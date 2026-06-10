<x-admin-layout :title="__('Store Management')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management')]]">
    <x-admin.page-header :title="__('Store Management')" :description="__('Warehouse operations, transfers, managers, and balances.')">
        <x-slot name="actions">
            @can('create', App\Models\Inventory\Warehouse::class)
                <a href="{{ route('admin.inventory.warehouses.create') }}" class="erp-btn-primary">{{ __('New warehouse') }}</a>
            @endcan
            @if (auth()->user()?->can('inventory.issue'))
                <a href="{{ route('admin.inventory.issues.create') }}" class="erp-btn-secondary">{{ __('New Stock Issue') }}</a>
            @endif
            @if (auth()->user()?->can('inventory.transfer'))
                <a href="{{ route('admin.inventory.transfers.create') }}" class="erp-btn-secondary" data-erp-modal-open>{{ __('Transfer stock') }}</a>
            @endif
            <a href="{{ route('admin.inventory.store.balances') }}" class="erp-btn-secondary">{{ __('Store balances') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Stores'), 'value' => $stats['stores'], 'icon' => 'building'],
            ['label' => __('Active Stores'), 'value' => $stats['active_stores'], 'icon' => 'badge-check'],
            ['label' => __('Store Managers'), 'value' => $stats['store_managers'], 'icon' => 'users'],
            ['label' => __('Pending Transfers'), 'value' => $stats['pending_transfers'], 'icon' => 'truck'],
            ['label' => __('Reorder Alerts'), 'value' => $stats['reorder_alerts'], 'icon' => 'bell'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <x-admin.data-table :search-placeholder="__('Search stores...')" export-filename="store-dashboard">
                <x-slot name="filters">
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <label class="text-xs font-medium text-slate-600">
                            {{ __('Status') }}
                            <select class="erp-select mt-1" x-model="filterValues.status">
                                <option value="all">{{ __('All') }}</option>
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                        </label>
                        <label class="text-xs font-medium text-slate-600">
                            {{ __('Managers') }}
                            <select class="erp-select mt-1" x-model="filterValues.manager">
                                <option value="all">{{ __('All') }}</option>
                                <option value="assigned">{{ __('Assigned') }}</option>
                                <option value="unassigned">{{ __('Unassigned') }}</option>
                            </select>
                        </label>
                    </div>
                </x-slot>
                <x-slot name="head">
                    <tr>
                        <th scope="col">{{ __('Store') }}</th>
                        <th scope="col">{{ __('Managers') }}</th>
                        <th scope="col">{{ __('Balance') }}</th>
                        <th scope="col">{{ __('Ledger Value') }}</th>
                        <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </x-slot>
                <x-slot name="body">
                    @forelse ($warehouses as $warehouse)
                        @php
                            $balance = $balances[$warehouse->id] ?? null;
                            $status = $warehouse->is_active ? 'active' : 'inactive';
                            $managerState = $warehouse->managers_count > 0 ? 'assigned' : 'unassigned';
                        @endphp
                        <tr x-show="rowVisible(@js(strtolower($warehouse->code.' '.$warehouse->name)), null, @js(['status' => $status, 'manager' => $managerState]), {{ $loop->iteration }})">
                            <td>
                                <div class="font-medium">{{ $warehouse->name }}</div>
                                <div class="font-mono text-[11px] text-slate-500">{{ $warehouse->code }}</div>
                            </td>
                            <td class="tabular-nums">{{ $warehouse->managers_count }}</td>
                            <td class="tabular-nums">{{ number_format((float) ($balance?->balance ?? 0), 3) }}</td>
                            <td class="tabular-nums">{{ number_format((float) ($balance?->stock_value ?? 0), 2) }}</td>
                            <td class="erp-table-actions-col">
                                <x-admin.table-row-actions>
                                    <x-admin.table-row-action :href="route('admin.inventory.warehouses.show', $warehouse)">{{ __('View warehouse') }}</x-admin.table-row-action>
                                    @can('update', $warehouse)
                                        <x-admin.table-row-action :href="route('admin.inventory.warehouses.edit', $warehouse)">{{ __('Edit warehouse') }}</x-admin.table-row-action>
                                        <x-admin.table-row-action :href="route('admin.inventory.warehouses.managers.edit', $warehouse)">{{ __('Manage managers') }}</x-admin.table-row-action>
                                    @endcan
                                    <x-admin.table-row-action :href="route('admin.inventory.warehouses.balances', $warehouse)">{{ __('View balances') }}</x-admin.table-row-action>
                                    <x-admin.table-row-action :href="route('admin.inventory.transfers.index', ['warehouse_id' => $warehouse->id])">{{ __('View transfers') }}</x-admin.table-row-action>
                                    @if (auth()->user()?->can('activity_logs.view'))
                                        <x-admin.table-row-action :href="route('admin.activity-logs.index')">{{ __('Audit history') }}</x-admin.table-row-action>
                                    @endif
                                    @can('update', $warehouse)
                                        @if ($warehouse->is_active)
                                            <x-admin.table-row-action method="PATCH" :action="route('admin.inventory.warehouses.deactivate', $warehouse)" variant="danger" :confirm="__('Deactivate this warehouse?')">{{ __('Deactivate warehouse') }}</x-admin.table-row-action>
                                        @else
                                            <x-admin.table-row-action method="PATCH" :action="route('admin.inventory.warehouses.reactivate', $warehouse)">{{ __('Reactivate warehouse') }}</x-admin.table-row-action>
                                        @endif
                                    @endcan
                                </x-admin.table-row-actions>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-admin.empty-state icon="building" :title="__('No stores configured')" /></td></tr>
                    @endforelse
                </x-slot>
            </x-admin.data-table>
        </div>

        <x-admin.card>
            <h2 class="text-sm font-semibold text-slate-900">{{ __('Low stock') }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($lowStockItems as $item)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span>
                            <span class="block font-medium">{{ $item->item_name }}</span>
                            <span class="block font-mono text-[11px] text-slate-500">{{ $item->sku }}</span>
                        </span>
                        <span class="tabular-nums text-slate-600">{{ number_format((float) $item->reorder_level, 3) }}</span>
                    </div>
                @empty
                    <x-admin.empty-state icon="badge-check" :title="__('No low-stock exceptions')" />
                @endforelse
            </div>
        </x-admin.card>
    </div>
</x-admin-layout>
