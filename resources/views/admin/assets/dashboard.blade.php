<x-admin-layout
    :title="__('Asset Dashboard')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Dashboard')],
    ]"
>
    <x-admin.page-header :title="__('Asset Dashboard')" :description="__('Asset KPIs and summaries.')" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <x-admin.kpi-widget :label="__('Total Assets')" :value="$stats['total_assets']" icon="chip" />
        <x-admin.kpi-widget :label="__('Total Asset Value')" :value="number_format($stats['total_asset_value'], 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Total Book Value')" :value="number_format($stats['total_book_value'], 2)" icon="chart-pie" />
        @can('maintenance.view')
            <x-admin.kpi-widget :label="__('Open Maintenance')" :value="$stats['maintenance']['open_work_orders'] ?? 0" icon="clipboard-list" />
            <x-admin.kpi-widget :label="__('Critical Failures')" :value="$stats['maintenance']['critical_failures'] ?? 0" icon="exclamation" />
            <x-admin.kpi-widget :label="__('Downtime Hours')" :value="$stats['maintenance']['downtime_hours'] ?? 0" icon="pause" />
        @endcan
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Assets By Category') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['by_category'] as $row)
                    <li class="flex justify-between"><span>{{ $row['name'] ?? $row->name }}</span><span class="font-medium">{{ $row['assets_count'] ?? $row->assets_count }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No categories yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Assets By Status') }}</h3>
            <ul class="space-y-2 text-sm">
                @foreach ($stats['by_status'] as $status => $count)
                    @if ($count > 0)
                        <li class="flex justify-between"><span>{{ ucfirst(str_replace('_', ' ', $status)) }}</span><span class="font-medium">{{ $count }}</span></li>
                    @endif
                @endforeach
            </ul>
        </x-admin.card>

        @if (count($stats['by_branch']) > 0)
            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold">{{ __('Assets By Branch') }}</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($stats['by_branch'] as $row)
                        <li class="flex justify-between"><span>{{ $row['name'] ?? $row->name }}</span><span class="font-medium">{{ $row['fixed_assets_count'] ?? $row->fixed_assets_count }}</span></li>
                    @endforeach
                </ul>
            </x-admin.card>
        @endif

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Recently Added Assets') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['recently_added'] as $asset)
                    <li><a href="{{ route('admin.assets.show', $asset) }}" class="erp-link">{{ $asset->asset_number }}</a> — {{ $asset->asset_name }}</li>
                @empty
                    <li class="text-slate-500">{{ __('No assets yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Recently Assigned Assets') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['recently_assigned'] as $history)
                    <li>
                        <a href="{{ route('admin.assets.show', $history->asset) }}" class="erp-link">{{ $history->asset?->asset_number }}</a>
                        — {{ $history->assignedUser?->name ?? $history->assignedBranch?->name }}
                    </li>
                @empty
                    <li class="text-slate-500">{{ __('No assignments yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>
</x-admin-layout>
