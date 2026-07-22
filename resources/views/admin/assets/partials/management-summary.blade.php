@php
    use App\Support\Navigation\WorkspaceEmbed;

    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

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

<div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('Assets By Category') }}</h3>
        <ul class="space-y-2 text-sm">
            @forelse ($stats['by_category'] as $row)
                <li class="flex justify-between gap-2">
                    <a
                        href="{{ WorkspaceEmbed::url(route('admin.assets.index', WorkspaceEmbed::queryParams(['category_id' => $row['id'] ?? $row->id]))) }}"
                        data-turbo-frame="{{ $turboFrame }}"
                        class="erp-link truncate"
                    >{{ $row['name'] ?? $row->name }}</a>
                    <span class="shrink-0 font-medium tabular-nums">{{ $row['assets_count'] ?? $row->assets_count }}</span>
                </li>
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
                    <li class="flex justify-between gap-2">
                        <a
                            href="{{ WorkspaceEmbed::url(route('admin.assets.index', WorkspaceEmbed::queryParams(['status' => $status]))) }}"
                            data-turbo-frame="{{ $turboFrame }}"
                            class="erp-link"
                        >{{ ucfirst(str_replace('_', ' ', $status)) }}</a>
                        <span class="font-medium tabular-nums">{{ $count }}</span>
                    </li>
                @endif
            @endforeach
        </ul>
    </x-admin.card>

    @if (count($stats['by_branch']) > 0)
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Assets By Branch') }}</h3>
            <ul class="space-y-2 text-sm">
                @foreach ($stats['by_branch'] as $row)
                    <li class="flex justify-between gap-2">
                        <a
                            href="{{ WorkspaceEmbed::url(route('admin.assets.index', WorkspaceEmbed::queryParams(['branch_id' => $row['id'] ?? $row->id]))) }}"
                            data-turbo-frame="{{ $turboFrame }}"
                            class="erp-link truncate"
                        >{{ $row['name'] ?? $row->name }}</a>
                        <span class="shrink-0 font-medium tabular-nums">{{ $row['fixed_assets_count'] ?? $row->fixed_assets_count }}</span>
                    </li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('Recently Added Assets') }}</h3>
        <ul class="space-y-2 text-sm">
            @forelse ($stats['recently_added'] as $asset)
                <li>
                    <a href="{{ route('admin.assets.show', $asset) }}" data-turbo-frame="erp-main" data-turbo-action="advance">{{ $asset->asset_number }}</a>
                    — {{ $asset->asset_name }}
                </li>
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
                    <a href="{{ route('admin.assets.show', $history->asset) }}" data-turbo-frame="erp-main" data-turbo-action="advance">{{ $history->asset?->asset_number }}</a>
                    — {{ $history->assignedUser?->name ?? $history->assignedBranch?->name }}
                </li>
            @empty
                <li class="text-slate-500">{{ __('No assignments yet.') }}</li>
            @endforelse
        </ul>
    </x-admin.card>
</div>
