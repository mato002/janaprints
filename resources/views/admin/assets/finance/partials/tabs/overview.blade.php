<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
    <x-admin.kpi-widget :label="__('Total Asset Cost')" :value="number_format($stats['total_asset_cost'], 2)" icon="chip" />
    <x-admin.kpi-widget :label="__('Net Book Value')" :value="number_format($stats['net_book_value'], 2)" icon="chart-pie" />
    <x-admin.kpi-widget :label="__('Accumulated Depreciation')" :value="number_format($stats['accumulated_depreciation'], 2)" icon="trending-down" />
    <x-admin.kpi-widget :label="__('Monthly Depreciation')" :value="number_format($stats['monthly_depreciation'], 2)" icon="calendar" />
    <x-admin.kpi-widget :label="__('Annual Depreciation')" :value="number_format($stats['annual_depreciation'], 2)" icon="calendar" />
    <x-admin.kpi-widget :label="__('Fully Depreciated')" :value="$stats['fully_depreciated_assets']" icon="check-circle" />
    <x-admin.kpi-widget :label="__('Near End of Life')" :value="$stats['near_end_of_life']" icon="exclamation" />
</div>

<div class="mt-6">
    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold">{{ __('Assets By Category') }}</h3>
        <ul class="space-y-2 text-sm">
            @forelse ($stats['by_category'] as $row)
                <li class="flex justify-between"><span>{{ data_get($row, 'category', __('Uncategorized')) }}</span><span>{{ number_format((float) data_get($row, 'nbv', 0), 2) }} NBV</span></li>
            @empty
                <li class="text-slate-500">{{ __('No category data.') }}</li>
            @endforelse
        </ul>
    </x-admin.card>
</div>
