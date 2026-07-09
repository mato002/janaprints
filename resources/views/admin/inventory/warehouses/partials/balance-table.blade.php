<x-admin.data-table class="mt-6" :search-placeholder="__('Search balances...')" export-filename="store-balances">
    <x-slot name="filters">
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
            <label class="text-xs font-medium text-slate-600">
                {{ __('Item category') }}
                <select class="erp-select mt-1" x-model="filterValues.category">
                    <option value="all">{{ __('All') }}</option>
                    @foreach (($categories ?? collect()) as $category)
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
            <tr x-show="rowVisible(@js(strtolower($line->sku.' '.$line->item_name.' '.($line->category_name ?? ''))), null, @js(['category' => $line->category_name ?? '', 'stock_state' => $stockStates]), {{ $loop->iteration }})">
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
                        <x-admin.table-row-action :href="route('admin.inventory.items.show', ['item' => $line->public_id ?? $line->id])">{{ __('View item') }}</x-admin.table-row-action>
                        <x-admin.table-row-action :href="route('admin.inventory.movements.index')">{{ __('View movements') }}</x-admin.table-row-action>
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-admin.empty-state icon="cube" :title="__('No stock movements yet')" /></td></tr>
        @endforelse
    </x-slot>
</x-admin.data-table>
