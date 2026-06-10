<x-admin-layout :title="__('Store Transfers')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Store Transfers')]]">
    <x-admin.page-header :title="__('Store Transfers')" :description="__('Move stock between warehouses using controlled inventory movements.')">
        <x-slot name="actions">
            @if (auth()->user()?->can('inventory.transfer'))
                <a href="{{ route('admin.inventory.transfers.create') }}" class="erp-btn-primary">{{ __('New transfer') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search transfers...')"
        export-route="admin.inventory.exports"
        :export-route-params="['listing' => 'transfers']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="store-transfers"
    >
        <x-slot name="filters">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3 xl:grid-cols-5">
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Status') }}
                    <select class="erp-select mt-1" x-model="filterValues.status">
                        <option value="all">{{ __('All') }}</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Source warehouse') }}
                    <select class="erp-select mt-1" x-model="filterValues.source">
                        <option value="all">{{ __('All') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Destination warehouse') }}
                    <select class="erp-select mt-1" x-model="filterValues.destination">
                        <option value="all">{{ __('All') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium text-slate-600">
                    {{ __('From date') }}
                    <input type="date" class="erp-input mt-1" x-model="filterValues.date_from">
                </label>
                <label class="text-xs font-medium text-slate-600">
                    {{ __('To date') }}
                    <input type="date" class="erp-input mt-1" x-model="filterValues.date_to">
                </label>
            </div>
        </x-slot>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Transfer') }}</th>
                <th scope="col">{{ __('From') }}</th>
                <th scope="col">{{ __('To') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($transfers as $transfer)
                <tr x-show="rowVisible(@js(strtolower($transfer->issue_number.' '.($transfer->warehouse?->name ?? '').' '.($transfer->toWarehouse?->name ?? '').' '.$transfer->status->value)), null, @js(['status' => $transfer->status->value, 'source' => (string) $transfer->warehouse_id, 'destination' => (string) $transfer->to_warehouse_id, 'date' => optional($transfer->issue_date)->format('Y-m-d')]), {{ $loop->iteration }})">
                    <td>
                        <div class="font-medium">{{ $transfer->issue_number }}</div>
                        <div class="text-xs text-slate-500">{{ $transfer->issue_date?->format('Y-m-d') }}</div>
                    </td>
                    <td>{{ $transfer->warehouse?->name ?? '-' }}</td>
                    <td>{{ $transfer->toWarehouse?->name ?? '-' }}</td>
                    <td><x-admin.enum-status-badge :status="$transfer->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.inventory.transfers.show', $transfer)">{{ __('View transfer') }}</x-admin.table-row-action>
                            @if ($transfer->warehouse)
                                <x-admin.table-row-action :href="route('admin.inventory.warehouses.show', $transfer->warehouse)">{{ __('View source warehouse') }}</x-admin.table-row-action>
                            @endif
                            @if ($transfer->toWarehouse)
                                <x-admin.table-row-action :href="route('admin.inventory.warehouses.show', $transfer->toWarehouse)">{{ __('View destination warehouse') }}</x-admin.table-row-action>
                            @endif
                            @can('post', $transfer)
                                <x-admin.table-row-action
                                    method="POST"
                                    :action="route('admin.inventory.transfers.post', $transfer)"
                                    :confirm="__('Post this transfer?')"
                                >{{ __('Post transfer') }}</x-admin.table-row-action>
                            @endcan
                            @if (auth()->user()?->can('activity_logs.view'))
                                <x-admin.table-row-action :href="route('admin.activity-logs.index')">{{ __('Audit history') }}</x-admin.table-row-action>
                            @endif
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="truck" :title="__('No transfers yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$transfers" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
