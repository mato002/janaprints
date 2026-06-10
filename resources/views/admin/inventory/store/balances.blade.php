<x-admin-layout :title="__('Store Balances')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Store Balances')]]">
    <x-admin.page-header :title="__('Store Balances')" :description="__('View stock position by item, warehouse, and branch.')" />

    <x-admin.data-table
        :search-placeholder="__('Search store balances...')"
        export-route="admin.inventory.exports"
        :export-route-params="['listing' => 'store-balances']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="store-balances"
    >
        <x-slot name="filters">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3 xl:grid-cols-4">
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Warehouse') }}
                    <select class="erp-select mt-1" x-model="filterValues.warehouse">
                        <option value="all">{{ __('All') }}</option>
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Item category') }}
                    <select class="erp-select mt-1" x-model="filterValues.category">
                        <option value="all">{{ __('All') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->name }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-xs font-medium text-slate-600">
                    {{ __('Stock state') }}
                    <select class="erp-select mt-1" x-model="filterValues.stock_state">
                        <option value="all">{{ __('All') }}</option>
                        <option value="low">{{ __('Low stock') }}</option>
                        <option value="zero">{{ __('Zero stock') }}</option>
                        <option value="negative">{{ __('Negative stock') }}</option>
                    </select>
                </label>
            </div>
        </x-slot>
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Warehouse') }}</th>
                <th scope="col">{{ __('Item') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Category') }}</th>
                <th scope="col">{{ __('Balance') }}</th>
                <th scope="col">{{ __('Reorder') }}</th>
                <th scope="col">{{ __('Value') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($balances as $line)
                @php
                    $balance = (float) $line->balance;
                    $stockStates = [];
                    if ($balance <= (float) $line->reorder_level) { $stockStates[] = 'low'; }
                    if ($balance == 0.0) { $stockStates[] = 'zero'; }
                    if ($balance < 0.0) { $stockStates[] = 'negative'; }
                @endphp
                <tr x-show="rowVisible(@js(strtolower($line->warehouse_code.' '.$line->warehouse_name.' '.$line->sku.' '.$line->item_name.' '.($line->category_name ?? ''))), null, @js(['warehouse' => (string) $line->warehouse_id, 'category' => $line->category_name ?? '', 'stock_state' => $stockStates]), {{ $loop->iteration }})">
                    <td>
                        <div class="font-medium">{{ $line->warehouse_name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $line->warehouse_code }}</div>
                    </td>
                    <td>
                        <div class="font-medium">{{ $line->item_name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $line->sku }}</div>
                    </td>
                    <td class="hidden md:table-cell">{{ $line->category_name ?? '-' }}</td>
                    <td class="tabular-nums">{{ number_format($balance, 3) }}</td>
                    <td class="tabular-nums">{{ number_format((float) $line->reorder_level, 3) }}</td>
                    <td class="tabular-nums">{{ number_format($balance * (float) $line->standard_cost, 2) }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.inventory.items.show', $line->item_id)">{{ __('View item') }}</x-admin.table-row-action>
                            <x-admin.table-row-action :href="route('admin.inventory.warehouses.show', $line->warehouse_id)">{{ __('View warehouse') }}</x-admin.table-row-action>
                            <x-admin.table-row-action :href="route('admin.inventory.warehouses.balances', $line->warehouse_id)">{{ __('View balances') }}</x-admin.table-row-action>
                            <x-admin.table-row-action :href="route('admin.inventory.movements.index')">{{ __('View movements') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state icon="cube" :title="__('No stock movements yet')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
