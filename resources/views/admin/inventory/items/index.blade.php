<x-admin-layout :title="__('Inventory items')" :breadcrumbs="[['label' => __('Inventory'), 'url' => route('admin.inventory.dashboard')], ['label' => __('Items')]]">
    <x-admin.page-header :title="__('Inventory items')">
        @can('create', App\Models\Inventory\InventoryItem::class)
            <a href="{{ route('admin.inventory.items.create') }}" class="erp-btn-primary">{{ __('New item') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.data-table :search-placeholder="__('Search inventory…')" export-filename="inventory-items">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Item') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Category') }}</th>
                <th scope="col">{{ __('Reorder') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($items as $item)
                <tr x-show="rowVisible(@js(strtolower($item->sku.' '.$item->item_name.' '.($item->category?->name ?? ''))))">
                    <td>
                        <div class="font-medium">{{ $item->item_name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $item->sku }}</div>
                    </td>
                    <td class="hidden md:table-cell">{{ $item->category?->name ?? '—' }}</td>
                    <td class="tabular-nums">{{ $item->reorder_level }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.inventory.items.show', $item)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $item)
                                <x-admin.table-row-action :href="route('admin.inventory.items.edit', $item)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state icon="cube" :title="__('No items yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$items" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
