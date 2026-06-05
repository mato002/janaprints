<x-admin-layout
    :title="__('Machine Operations Dashboard')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Machine Operations Dashboard')],
    ]"
>
    <x-admin.page-header :title="__('Machine Operations Dashboard')" :description="__('Machine availability, utilization, and capacity overview.')">
        <x-slot name="actions">
            <a href="{{ route('admin.assets.machines.index') }}" class="erp-btn-secondary">{{ __('All Machines') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Total Machines')" :value="$stats['total_machines']" icon="cog" />
        <x-admin.kpi-widget :label="__('Available')" :value="$stats['available_machines']" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Running')" :value="$stats['running_machines']" icon="play" />
        <x-admin.kpi-widget :label="__('Offline')" :value="$stats['offline_machines']" icon="pause" />
        <x-admin.kpi-widget :label="__('Maintenance Holds')" :value="$stats['maintenance_holds']" icon="cog" />
        <x-admin.kpi-widget :label="__('Utilization')" :value="$stats['utilization_percent'].'%'" icon="chart-pie" />
        <x-admin.kpi-widget :label="__('Capacity')" :value="$stats['capacity_percent'].'%'" icon="chart-pie" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Machines By Type') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['by_type'] as $row)
                    <li class="flex justify-between"><span>{{ $row->machine_type }}</span><span class="font-medium">{{ $row->count }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No machines yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Machines By Branch') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['by_branch'] as $row)
                    <li class="flex justify-between"><span>{{ __('Branch') }} #{{ $row->branch_id }}</span><span class="font-medium">{{ $row->count }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No branch data.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card class="lg:col-span-2">
            <h3 class="mb-3 text-sm font-semibold">{{ __('Recently Assigned Machines') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['recently_assigned'] as $profile)
                    <li>
                        <a href="{{ route('admin.assets.machines.show', $profile->fixed_asset_id) }}" class="erp-link">
                            {{ $profile->asset?->asset_name }}
                        </a>
                        <span class="text-slate-500"> — {{ $profile->machine_code }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No recent machine assignments.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
</x-admin-layout>
