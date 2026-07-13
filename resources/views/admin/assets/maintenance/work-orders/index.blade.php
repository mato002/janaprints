<x-admin-layout :title="__('Work Orders')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Work Orders')]]">
    <x-admin.page-header :title="__('Maintenance Work Orders')" :description="__('Preventive, corrective, and emergency maintenance.')">
        <x-slot name="actions">
            @can('create', \App\Models\Assets\MaintenanceWorkOrder::class)
                <a href="{{ route('admin.assets.maintenance.work-orders.create') }}" class="erp-btn-primary">{{ __('New work order') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <input type="search" name="search" value="{{ $filters['search'] }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search work orders…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}" @selected($filters['status'] === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
            <select name="priority" class="erp-toolbar-select" aria-label="{{ __('Priority') }}">
                <option value="">{{ __('All priorities') }}</option>
                @foreach ($priorities as $p)
                    <option value="{{ $p->value }}" @selected($filters['priority'] === $p->value)>{{ $p->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :searchable="false"
        :exportable="true"
        export-filename="maintenance-work-orders"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Work order') }}</th>
                <th scope="col">{{ __('Asset') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Priority') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col">{{ __('Scheduled') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($work_orders as $order)
                <tr>
                    <td class="font-mono font-medium">{{ $order->work_order_no }}</td>
                    <td>{{ $order->asset?->asset_name }}</td>
                    <td>{{ $order->maintenance_type->label() }}</td>
                    <td><x-admin.status-badge :variant="$order->priority->badgeVariant()">{{ $order->priority->label() }}</x-admin.status-badge></td>
                    <td><x-admin.status-badge :variant="$order->status->badgeVariant()">{{ $order->status->label() }}</x-admin.status-badge></td>
                    <td class="whitespace-nowrap">{{ $order->scheduled_for?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.assets.maintenance.work-orders.show', $order)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-admin.empty-state icon="clipboard-list" :title="__('No work orders yet')" :description="__('Create a work order to schedule maintenance.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$work_orders" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
