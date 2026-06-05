<x-admin-layout :title="__('Asset Finance Dashboard')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Asset Finance Dashboard')]]">
    <x-admin.page-header :title="__('Asset Finance Dashboard')" :description="__('Fixed asset valuation and depreciation overview.')">
        <x-slot name="actions">
            @can('run', \App\Models\Assets\DepreciationRun::class)
                <a href="{{ route('admin.assets.finance.runs.create') }}" class="erp-btn-primary">{{ __('New Depreciation Run') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Total Asset Cost')" :value="number_format($stats['total_asset_cost'], 2)" icon="chip" />
        <x-admin.kpi-widget :label="__('Net Book Value')" :value="number_format($stats['net_book_value'], 2)" icon="chart-pie" />
        <x-admin.kpi-widget :label="__('Accumulated Depreciation')" :value="number_format($stats['accumulated_depreciation'], 2)" icon="trending-down" />
        <x-admin.kpi-widget :label="__('Monthly Depreciation')" :value="number_format($stats['monthly_depreciation'], 2)" icon="calendar" />
        <x-admin.kpi-widget :label="__('Annual Depreciation')" :value="number_format($stats['annual_depreciation'], 2)" icon="calendar" />
        <x-admin.kpi-widget :label="__('Fully Depreciated')" :value="$stats['fully_depreciated_assets']" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Near End of Life')" :value="$stats['near_end_of_life']" icon="exclamation" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Assets By Category') }}</h3>
            <ul class="space-y-2 text-sm">
                @forelse ($stats['by_category'] as $row)
                    <li class="flex justify-between"><span>{{ $row['category'] ?? __('Uncategorized') }}</span><span>{{ number_format($row['nbv'], 2) }} NBV</span></li>
                @empty
                    <li class="text-slate-500">{{ __('No category data.') }}</li>
                @endforelse
            </ul>
        </x-admin.card>
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold">{{ __('Quick Links') }}</h3>
            <div class="flex flex-col gap-2 text-sm">
                <a href="{{ route('admin.assets.finance.runs.index') }}" class="erp-link">{{ __('Depreciation Runs') }}</a>
                <a href="{{ route('admin.assets.finance.entries.index') }}" class="erp-link">{{ __('Depreciation Entries') }}</a>
                <a href="{{ route('admin.assets.finance.reconciliation.index') }}" class="erp-link">{{ __('Reconciliation') }}</a>
                <a href="{{ route('admin.assets.finance.reports.index') }}" class="erp-link">{{ __('Asset Reports') }}</a>
            </div>
        </x-admin.card>
    </div>
</x-admin-layout>
