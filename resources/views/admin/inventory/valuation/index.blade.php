<x-admin-layout :title="__('Stock Valuation')" :breadcrumbs="[['label' => __('Inventory'), 'url' => route('admin.inventory.dashboard')], ['label' => __('Valuation')]]">
    <x-admin.page-header :title="__('Inventory Valuation')" :description="__('As at :date', ['date' => $valuationDate])">
        <x-slot name="actions">
            <form method="POST" action="{{ route('admin.inventory.valuation.snapshot') }}" class="inline">
                @csrf
                <input type="hidden" name="valuation_date" value="{{ $valuationDate }}">
                <input type="hidden" name="scope" value="branch">
                <button type="submit" class="erp-btn-secondary">{{ __('Save snapshot') }}</button>
            </form>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('FIFO value (layers)')" :value="number_format($totals['fifo_total'], 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Average cost value')" :value="number_format($totals['average_total'], 2)" icon="calculator" />
        <x-admin.kpi-widget :label="__('GL inventory (:code)', ['code' => $reconciliation['account_code']])" :value="number_format($reconciliation['gl_balance'], 2)" icon="book-open" />
        <x-admin.kpi-widget :label="__('GL vs FIFO variance')" :value="number_format($reconciliation['variance'], 2)" icon="scale" />
    </div>

    <x-admin.card class="mt-4 flex flex-wrap gap-2 text-sm">
        @foreach (['item' => __('By item'), 'warehouse' => __('By warehouse'), 'category' => __('By category'), 'branch' => __('By branch')] as $key => $label)
            <a href="{{ route('admin.inventory.valuation.index', ['scope' => $key, 'date' => $valuationDate]) }}"
               class="{{ $scope === $key ? 'erp-btn-primary' : 'erp-btn-secondary' }}">{{ $label }}</a>
        @endforeach
    </x-admin.card>

    <x-admin.card class="mt-4">
        @if ($scope === 'item')
            <table class="erp-table text-sm">
                <thead><tr><th>{{ __('Item') }}</th><th>{{ __('Qty') }}</th><th>{{ __('FIFO') }}</th><th>{{ __('Avg cost') }}</th></tr></thead>
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
        @elseif ($scope === 'warehouse')
            <table class="erp-table text-sm">
                <thead><tr><th>{{ __('Warehouse') }}</th><th>{{ __('Qty') }}</th><th>{{ __('FIFO') }}</th><th>{{ __('Avg cost') }}</th></tr></thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['warehouse']->name }}</td>
                            <td>{{ number_format($row['quantity'], 3) }}</td>
                            <td>{{ number_format($row['fifo_value'], 2) }}</td>
                            <td>{{ number_format($row['average_cost_value'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @elseif ($scope === 'category')
            <table class="erp-table text-sm">
                <thead><tr><th>{{ __('Category') }}</th><th>{{ __('Items') }}</th><th>{{ __('Qty') }}</th><th>{{ __('FIFO') }}</th><th>{{ __('Avg cost') }}</th></tr></thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['category_name'] }}</td>
                            <td>{{ $row['item_count'] }}</td>
                            <td>{{ number_format($row['quantity'], 3) }}</td>
                            <td>{{ number_format($row['fifo_value'], 2) }}</td>
                            <td>{{ number_format($row['average_cost_value'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table class="erp-table text-sm">
                <thead><tr><th>{{ __('Branch') }}</th><th>{{ __('Items') }}</th><th>{{ __('FIFO') }}</th><th>{{ __('Avg cost') }}</th></tr></thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            <td>{{ $row['branch']->name }}</td>
                            <td>{{ $row['item_count'] }}</td>
                            <td>{{ number_format($row['fifo_value'], 2) }}</td>
                            <td>{{ number_format($row['average_cost_value'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-admin.card>

    @if ($totals['top_items']->isNotEmpty())
        <x-admin.card class="mt-4">
            <h3 class="mb-3 text-sm font-semibold">{{ __('Top value items') }}</h3>
            <ul class="space-y-1 text-sm">
                @foreach ($totals['top_items'] as $row)
                    <li>{{ $row['item']->item_name }} — {{ number_format($row['fifo_value'], 2) }}</li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif
</x-admin-layout>
