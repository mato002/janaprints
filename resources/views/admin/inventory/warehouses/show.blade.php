<x-admin-layout :title="$warehouse->name" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Warehouse Profile')]]">
    <x-admin.page-header :title="$warehouse->name" :description="$warehouse->description ?: __('Warehouse profile, managers, balances, and custody status.')">
        <x-slot name="actions">
            <x-admin.status-badge :variant="$warehouse->is_active ? 'success' : 'neutral'">{{ $warehouse->is_active ? __('Active') : __('Inactive') }}</x-admin.status-badge>
            @if ($warehouse->is_active && auth()->user()?->can('inventory.issue'))
                <a href="{{ route('admin.inventory.issues.create', ['warehouse_id' => $warehouse->id]) }}" class="erp-btn-secondary">{{ __('New Stock Issue') }}</a>
            @endif
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
                        <x-admin.table-row-action method="PATCH" :action="route('admin.inventory.warehouses.deactivate', $warehouse)" variant="danger" :confirm="__('Deactivate this warehouse? Existing history will be preserved.')">{{ __('Deactivate warehouse') }}</x-admin.table-row-action>
                    @else
                        <x-admin.table-row-action method="PATCH" :action="route('admin.inventory.warehouses.reactivate', $warehouse)">{{ __('Reactivate warehouse') }}</x-admin.table-row-action>
                    @endif
                @endcan
                @can('delete', $warehouse)
                    <x-admin.table-row-action method="DELETE" :action="route('admin.inventory.warehouses.destroy', $warehouse)" variant="danger" :confirm="__('Remove this warehouse only if it has no operational history?')">{{ __('Remove warehouse') }}</x-admin.table-row-action>
                @endcan
            </x-admin.table-row-actions>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-admin.card>
            <div class="text-xs font-medium uppercase text-slate-500">{{ __('Code') }}</div>
            <div class="mt-1 font-mono text-sm">{{ $warehouse->code }}</div>
        </x-admin.card>
        <x-admin.card>
            <div class="text-xs font-medium uppercase text-slate-500">{{ __('Managers') }}</div>
            <div class="mt-1 text-sm">
                {{ $warehouse->managers->pluck('name')->join(', ') ?: __('No managers assigned') }}
            </div>
        </x-admin.card>
        <x-admin.card>
            <div class="text-xs font-medium uppercase text-slate-500">{{ __('Balance lines') }}</div>
            <div class="mt-1 text-sm tabular-nums">{{ $balances->count() }}</div>
        </x-admin.card>
    </div>

    @include('admin.inventory.warehouses.partials.balance-table', ['balances' => $balances])
</x-admin-layout>
