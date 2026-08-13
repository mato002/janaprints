<x-admin-layout :title="__('Inventory items')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'catalogue'])], ['label' => __('Products')]]">
    <x-admin.page-header :title="__('Products')">
        <x-slot name="actions">
            @can('create', App\Models\Inventory\InventoryItem::class)
                <a href="{{ route('admin.inventory.items.create') }}" class="erp-btn-primary">{{ __('New item') }}</a>
            @elsecan('create', App\Models\Sales\Quotation::class)
                <a href="{{ route('admin.sales.desk', ['view' => 'quotes']) }}" class="erp-btn-primary" data-turbo-frame="erp-main">{{ __('Create quote') }}</a>
            @elsecan('create', App\Models\Sales\SalesOrder::class)
                <a href="{{ route('admin.sales.desk') }}" class="erp-btn-primary" data-turbo-frame="erp-main">{{ __('Sales desk') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (auth()->user()?->can('catalogue.view') && ! auth()->user()?->can('catalogue.create') && ! auth()->user()?->can('catalogue.edit') && ! auth()->user()?->can('inventory.classification.manage'))
        <div class="mb-4 rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
            {{ __('Read-only catalogue access.') }}
            @can('create', App\Models\Sales\Quotation::class)
                <a href="{{ route('admin.sales.desk', ['view' => 'quotes']) }}" class="font-semibold text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ __('Create a quote') }}</a>
                {{ __('or') }}
                <a href="{{ route('admin.sales.desk') }}" class="font-semibold text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ __('start a walk-in') }}</a>
                {{ __('on the Sales Desk.') }}
            @else
                {{ __('Contact a store or catalogue administrator to add new products.') }}
            @endcan
        </div>
    @endif

    <x-admin.data-table
        :search-placeholder="__('Search inventory...')"
        export-route="admin.inventory.exports"
        :export-route-params="['listing' => 'items']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="inventory-items"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Item') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Role') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Category') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Brand') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Image') }}</th>
                <th scope="col">{{ __('Reorder') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($items as $item)
                <tr x-show="rowVisible(@js(strtolower($item->sku.' '.$item->item_name.' '.($item->category?->name ?? '').' '.($item->subcategory?->name ?? '').' '.($item->brand_name ?? $item->brand?->name ?? ''))))">
                    <td>
                        <div class="font-medium">{{ $item->item_name }}</div>
                        <div class="font-mono text-[11px] text-slate-500">{{ $item->sku }}</div>
                    </td>
                    <td class="hidden md:table-cell">
                        @if($item->stock_role)
                            <span class="erp-badge {{ $item->stock_role->badgeClass() }}">{{ $item->stock_role->label() }}</span>
                        @endif
                    </td>
                    <td class="hidden md:table-cell">{{ $item->category?->name ?? '-' }} @if($item->subcategory)<span class="block text-xs text-slate-500">{{ $item->subcategory->name }}</span>@endif</td>
                    <td class="hidden lg:table-cell">{{ $item->brand_name ?? $item->brand?->name ?? '-' }}</td>
                    <td class="hidden lg:table-cell">{{ $item->images->isNotEmpty() ? __('Yes') : __('Missing') }}</td>
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
                <tr><td colspan="7"><x-admin.empty-state icon="cube" :title="__('No items yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$items" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
