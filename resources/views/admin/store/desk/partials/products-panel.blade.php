@php
    use App\Enums\InventoryStockRole;
    use App\Support\Inventory\StoreDeskViews;
@endphp

<form method="GET" action="{{ StoreDeskViews::deskUrl(StoreDeskViews::PRODUCTS) }}" class="mb-4 flex flex-wrap items-end gap-3">
    <label class="min-w-[14rem] flex-1 text-xs font-medium text-slate-600">
        {{ __('Search') }}
        <input type="search" name="search" value="{{ $search ?? '' }}" class="erp-input mt-1 w-full" placeholder="{{ __('SKU or item name…') }}">
    </label>
    <label class="text-xs font-medium text-slate-600">
        {{ __('Stock role') }}
        <select name="stock_role" class="erp-select mt-1">
            <option value="all" @selected(($stockRole ?? 'all') === 'all')>{{ __('All roles') }}</option>
            @foreach ($stockRoles as $role)
                <option value="{{ $role->value }}" @selected(($stockRole ?? 'all') === $role->value)>{{ $role->label() }}</option>
            @endforeach
        </select>
    </label>
    <button type="submit" class="erp-btn-secondary text-sm">{{ __('Filter') }}</button>
</form>

<x-admin.data-table :search-placeholder="__('Search products...')">
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Item') }}</th>
            <th scope="col" class="hidden md:table-cell">{{ __('Role') }}</th>
            <th scope="col" class="hidden md:table-cell">{{ __('Category') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($items as $item)
            <tr x-show="rowVisible(@js(strtolower($item->sku.' '.$item->item_name.' '.($item->category?->name ?? '').' '.($item->stock_role?->label() ?? ''))))">
                <td>
                    <a href="{{ route('admin.inventory.items.show', $item) }}" class="font-medium text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ $item->item_name }}</a>
                    <div class="font-mono text-[11px] text-slate-500">{{ $item->sku }}</div>
                </td>
                <td class="hidden md:table-cell">
                    @if ($item->stock_role)
                        <span class="erp-badge {{ $item->stock_role->badgeClass() }}">{{ $item->stock_role->label() }}</span>
                    @else
                        —
                    @endif
                </td>
                <td class="hidden md:table-cell">{{ $item->category?->name ?? '—' }}</td>
                <td class="erp-table-actions-col">
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        @if (($item->stock_role ?? null) !== InventoryStockRole::FinishedGood)
                            @include('admin.inventory.items.partials.set-finished-good-form', [
                                'item' => $item,
                                'from' => 'store-desk',
                                'buttonClass' => 'erp-btn-ghost py-1 text-xs',
                            ])
                        @endif
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.inventory.items.show', $item)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $item)
                                <x-admin.table-row-action :href="route('admin.inventory.items.edit', $item)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <x-admin.empty-state icon="cube" :title="__('No products found')" />
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$items" /></x-slot>
</x-admin.data-table>
