<x-admin-layout :title="__('Inventory')" :breadcrumbs="[['label' => __('Inventory')]]">
    <x-admin.page-header :title="__('Inventory & Store')" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ([
            ['label' => __('Low Stock Items'), 'value' => $stats['low_stock'], 'icon' => 'exclamation'],
            ['label' => __('Inventory Value'), 'value' => number_format($stats['inventory_value'], 2), 'icon' => 'currency-dollar'],
            ['label' => __('Recent Receipts'), 'value' => $stats['recent_receipts'], 'icon' => 'archive'],
            ['label' => __('Recent Issues'), 'value' => $stats['recent_issues'], 'icon' => 'switch-horizontal'],
            ['label' => __('Reorder Alerts'), 'value' => $stats['reorder_alerts'], 'icon' => 'bell'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card class="mt-6 flex flex-wrap gap-2">
        @can('create', App\Models\Inventory\InventoryItem::class)
            <a href="{{ route('admin.inventory.items.create') }}" class="erp-btn-primary">{{ __('New item') }}</a>
        @endcan
        @can('create', App\Models\Inventory\StockReceipt::class)
            <a href="{{ route('admin.inventory.receipts.create') }}" class="erp-btn-secondary">{{ __('Stock receipt') }}</a>
        @endcan
        @can('create', App\Models\Inventory\StockIssue::class)
            <a href="{{ route('admin.inventory.issues.create') }}" class="erp-btn-secondary">{{ __('Stock issue') }}</a>
        @endcan
    </x-admin.card>
</x-admin-layout>
