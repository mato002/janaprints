<x-admin-layout :title="__('Warehouses')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Warehouses')]]">
    <x-admin.page-header :title="__('Warehouses')" :description="__('Manage stores, warehouse locations, branch stores, and stock custody.')">
        <x-slot name="actions">
            @can('create', App\Models\Inventory\Warehouse::class)
                <a href="{{ route('admin.inventory.warehouses.create') }}" class="erp-btn-primary">{{ __('New warehouse') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search warehouses...')"
        export-route="admin.inventory.exports"
        :export-route-params="['listing' => 'warehouses']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="warehouses"
    >
        <x-slot name="filters">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Status') }}
                    <select class="erp-select mt-1" x-model="filterValues.status">
                        <option value="all">{{ __('All statuses') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </select>
                </label>
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Branch') }}
                    <select class="erp-select mt-1" x-model="filterValues.branch">
                        <option value="all">{{ __('All branches') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Managers') }}
                    <select class="erp-select mt-1" x-model="filterValues.manager">
                        <option value="all">{{ __('All managers') }}</option>
                        <option value="assigned">{{ __('Assigned') }}</option>
                        <option value="unassigned">{{ __('Unassigned') }}</option>
                    </select>
                </label>
            </div>
        </x-slot>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Warehouse') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Branch') }}</th>
                <th scope="col">{{ __('Managers') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($warehouses as $warehouse)
                @php
                    $status = $warehouse->is_active ? 'active' : 'inactive';
                    $managerState = $warehouse->managers_count > 0 ? 'assigned' : 'unassigned';
                @endphp
                <tr x-show="rowVisible(@js(strtolower($warehouse->code.' '.$warehouse->name.' '.($warehouse->branch?->name ?? '').' '.$warehouse->managers->pluck('name')->join(' '))), null, @js(['status' => $status, 'branch' => (string) $warehouse->branch_id, 'manager' => $managerState]), {{ $loop->iteration }})">
                    <td>
                        <div class="font-medium">{{ $warehouse->name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $warehouse->code }}</div>
                    </td>
                    <td class="hidden md:table-cell">{{ $warehouse->branch?->name ?? '-' }}</td>
                    <td class="tabular-nums">{{ $warehouse->managers_count }}</td>
                    <td><x-admin.status-badge :variant="$warehouse->is_active ? 'success' : 'neutral'">{{ $warehouse->is_active ? __('Active') : __('Inactive') }}</x-admin.status-badge></td>
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
                                    <x-admin.table-row-action
                                        method="PATCH"
                                        :action="route('admin.inventory.warehouses.deactivate', $warehouse)"
                                        variant="danger"
                                        :confirm="__('Deactivate this warehouse? Existing history will be preserved.')"
                                    >{{ __('Deactivate warehouse') }}</x-admin.table-row-action>
                                @else
                                    <x-admin.table-row-action method="PATCH" :action="route('admin.inventory.warehouses.reactivate', $warehouse)">{{ __('Reactivate warehouse') }}</x-admin.table-row-action>
                                @endif
                            @endcan
                            @can('delete', $warehouse)
                                <x-admin.table-row-action
                                    method="DELETE"
                                    :action="route('admin.inventory.warehouses.destroy', $warehouse)"
                                    variant="danger"
                                    :confirm="__('Remove this warehouse only if it has no operational history?')"
                                >{{ __('Remove warehouse') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="building" :title="__('No warehouses yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$warehouses" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
