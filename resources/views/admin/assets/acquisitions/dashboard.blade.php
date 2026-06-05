<x-admin-layout :title="__('Asset Acquisition Dashboard')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Acquisition Dashboard')]]">
    <x-admin.page-header :title="__('Asset Acquisition Dashboard')" :description="__('Procurement-to-asset capitalization overview.')">
        <x-slot name="actions">
            @can('capitalize', \App\Models\Assets\AssetCapitalizationCandidate::class)
                <a href="{{ route('admin.assets.acquisitions.queue') }}" class="erp-btn-primary">{{ __('Capitalization Queue') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Pending Capitalization')" :value="$stats['pending_capitalization']" icon="inbox" />
        <x-admin.kpi-widget :label="__('Capitalized This Month')" :value="number_format($stats['capitalized_this_month'], 2)" icon="chip" />
        <x-admin.kpi-widget :label="__('Capitalized This Year')" :value="number_format($stats['capitalized_this_year'], 2)" icon="chart-pie" />
        <x-admin.kpi-widget :label="__('Warranty Expiring Soon')" :value="$stats['warranty_expiring_soon']" icon="shield-check" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('By Category') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['by_category'] as $row)
                    <li class="flex justify-between"><span>{{ $row['category'] }}</span><span>{{ number_format($row['total'], 2) }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No data yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('By Vendor') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['by_vendor'] as $row)
                    <li class="flex justify-between"><span>{{ $row['vendor'] }}</span><span>{{ number_format($row['total'], 2) }}</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No data yet.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
    </div>

    <x-admin.card class="mt-6">
        <h3 class="mb-3 text-sm font-semibold">{{ __('Recent Acquisitions') }}</h3>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Asset') }}</th>
                        <th>{{ __('Vendor') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Cost') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stats['recent_acquisitions'] as $asset)
                        <tr>
                            <td><a href="{{ route('admin.assets.show', $asset) }}" class="erp-link">{{ $asset->asset_name }}</a></td>
                            <td>{{ $asset->vendor?->vendor_name ?? '—' }}</td>
                            <td>{{ $asset->category?->name ?? '—' }}</td>
                            <td>{{ number_format($asset->acquisition_cost, 2) }}</td>
                            <td>{{ $asset->capitalization_date?->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-slate-500">{{ __('No procurement acquisitions yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
