<x-admin-layout
    :title="__('Maintenance Dashboard')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Maintenance Dashboard')],
    ]"
>
    <x-admin.page-header :title="__('Maintenance Operations Dashboard')" :description="__('Work orders, downtime, and preventive maintenance overview.')">
        <x-slot name="actions">
            @can('create', \App\Models\Assets\MaintenanceWorkOrder::class)
                <a href="{{ route('admin.assets.maintenance.work-orders.create') }}" class="erp-btn-primary">{{ __('New Work Order') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Open Work Orders')" :value="$stats['open_work_orders']" icon="clipboard-list" />
        <x-admin.kpi-widget :label="__('Completed')" :value="$stats['completed_work_orders']" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Overdue Maintenance')" :value="$stats['overdue_maintenance']" icon="exclamation" />
        <x-admin.kpi-widget :label="__('Machines Under Maintenance')" :value="$stats['machines_under_maintenance']" icon="cog" />
        <x-admin.kpi-widget :label="__('Downtime Hours')" :value="$stats['downtime_hours']" icon="pause" />
        <x-admin.kpi-widget :label="__('Critical Failures')" :value="$stats['critical_failures']" icon="exclamation" />
        <x-admin.kpi-widget :label="__('Upcoming Maintenance')" :value="$stats['upcoming_maintenance']" icon="calendar" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Critical Work Orders') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['critical_orders'] as $order)
                    <li>
                        <a href="{{ route('admin.assets.maintenance.work-orders.show', $order['id']) }}" class="erp-link font-mono">{{ $order['work_order_no'] }}</a>
                        — {{ $order['asset_name'] }}
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No critical work orders.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Recently Closed') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['recently_closed'] as $order)
                    <li>
                        <a href="{{ route('admin.assets.maintenance.work-orders.show', $order['id']) }}" class="erp-link">{{ $order['work_order_no'] }}</a>
                        — {{ $order['asset_name'] }}
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No recently closed work orders.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Maintenance By Asset Type') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['by_asset_type'] as $row)
                    <li class="flex justify-between"><span>{{ ucfirst(str_replace('_', ' ', $row->asset_type)) }}</span><span class="font-medium">{{ $row->count }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No data yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Maintenance By Branch') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['by_branch'] as $row)
                    <li class="flex justify-between"><span>{{ __('Branch') }} #{{ $row->branch_id }}</span><span class="font-medium">{{ $row->count }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No branch data.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
</x-admin-layout>
