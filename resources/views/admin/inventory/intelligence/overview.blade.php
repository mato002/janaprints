<x-admin-layout :title="__('Inventory Intelligence')" :breadcrumbs="[
    ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
    ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
    ['label' => __('Inventory Intelligence')],
]">
    <x-admin.page-header :title="__('Inventory Intelligence')" :description="__('Velocity, stockout risk, and dead stock insights from movement ledger data.')">
        <x-slot name="actions">
            @can('inventory.intelligence.generate')
                <form method="POST" action="{{ route('admin.inventory.intelligence.generate') }}" class="inline">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Refresh snapshots') }}</button>
                </form>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @include('admin.inventory.intelligence.partials.nav')

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ([
            ['label' => __('Critical stockout'), 'value' => $counts['critical_stockout'], 'icon' => 'exclamation'],
            ['label' => __('High risk'), 'value' => $counts['high_risk'], 'icon' => 'bell'],
            ['label' => __('Dead stock items'), 'value' => $counts['dead_stock'], 'icon' => 'archive'],
            ['label' => __('Fast moving'), 'value' => $counts['fast_moving'], 'icon' => 'switch-horizontal'],
            ['label' => __('Dead stock value'), 'value' => number_format($counts['dead_stock_value'], 2), 'icon' => 'currency-dollar'],
            ['label' => __('Avg days to depletion'), 'value' => $counts['average_days_to_depletion'] ?? '—', 'icon' => 'calendar'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        @include('admin.inventory.intelligence.partials.snapshot-table', [
            'title' => __('Top stockout risks'),
            'snapshots' => $topStockoutRisks,
            'empty' => __('No stockout risk snapshots yet. Run velocity snapshot generation.'),
        ])

        @include('admin.inventory.intelligence.partials.dead-stock-table', [
            'title' => __('Top dead stock items'),
            'rows' => $topDeadStock,
            'empty' => __('No dead stock detected.'),
        ])

        @include('admin.inventory.intelligence.partials.snapshot-table', [
            'title' => __('Fastest moving raw materials'),
            'snapshots' => $fastRawMaterials,
            'empty' => __('No fast-moving raw materials in current window.'),
        ])

        @include('admin.inventory.intelligence.partials.snapshot-table', [
            'title' => __('Stagnant finished goods'),
            'snapshots' => $stagnantFinishedGoods,
            'empty' => __('No stagnant finished goods detected.'),
        ])
    </div>
</x-admin-layout>
