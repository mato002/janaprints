<x-admin-layout :title="__('Assets')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Assets')]]">
    <x-admin.page-header :title="__('Asset Management')" />
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Asset cost')" :value="number_format($stats['totals']['asset_value'], 2)" icon="chip" />
        <x-admin.kpi-widget :label="__('Accumulated depreciation')" :value="number_format($stats['totals']['accumulated'], 2)" icon="trending-down" />
        <x-admin.kpi-widget :label="__('Net book value')" :value="number_format($stats['totals']['net_book_value'], 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Due for service')" :value="$stats['due_service']" icon="wrench" />
        <x-admin.kpi-widget :label="__('Under repair')" :value="$stats['under_repair']" icon="cog" />
        <x-admin.kpi-widget :label="__('Disposed')" :value="$stats['disposed']" icon="archive" />
    </div>
    <x-admin.card class="mt-6">
        <a href="{{ route('admin.assets.index') }}" class="erp-btn-primary">{{ __('Asset register') }}</a>
        <a href="{{ route('admin.assets.categories.index') }}" class="erp-btn-secondary ml-2">{{ __('Categories') }}</a>
        @can('assets.create')
            <a href="{{ route('admin.assets.create') }}" class="erp-btn-secondary ml-2">{{ __('Register asset') }}</a>
        @endcan
    </x-admin.card>
</x-admin-layout>
