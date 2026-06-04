<x-admin-layout :title="__('Stock Valuation')" :breadcrumbs="[['label' => __('Inventory'), 'url' => route('admin.inventory.dashboard')], ['label' => __('Valuation')]]">
    <x-admin.page-header :title="__('Inventory Valuation')" />
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('FIFO value')" :value="number_format($totals['fifo_total'], 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Average cost value')" :value="number_format($totals['average_total'], 2)" icon="calculator" />
        <x-admin.kpi-widget :label="__('Dead stock value')" :value="number_format($totals['dead_stock_value'], 2)" icon="archive" />
        <x-admin.kpi-widget :label="__('Items valued')" :value="$totals['item_count']" icon="cube" />
    </div>
    <x-admin.card class="mt-6">
        <table class="erp-table text-sm">
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th>{{ __('Qty') }}</th>
                    <th>{{ __('FIFO') }}</th>
                    <th>{{ __('Avg cost') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td>{{ $row['item']->item_name }}</td>
                        <td>{{ number_format($row['quantity'], 3) }}</td>
                        <td>{{ number_format($row['fifo_value'], 2) }}</td>
                        <td>{{ number_format($row['average_cost_value'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
