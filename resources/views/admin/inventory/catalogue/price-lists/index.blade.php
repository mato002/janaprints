<x-admin-layout :title="__('Price Lists')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Price Lists')]]">
    <x-admin.page-header :title="__('Price Lists')"><x-slot name="actions">@if (auth()->user()?->can('catalogue.create'))<a href="{{ route('admin.inventory.catalogue.price-lists.create') }}" class="erp-btn-primary">{{ __('New Price List') }}</a>@endif</x-slot></x-admin.page-header>
    <x-admin.data-table
        :search-placeholder="__('Search price lists...')"
        export-route="admin.inventory.exports"
        :export-route-params="['listing' => 'price-lists']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="price-lists"
    >
        <x-slot name="head"><tr><th>{{ __('Name') }}</th><th>{{ __('Currency') }}</th><th>{{ __('Effective') }}</th><th>{{ __('Status') }}</th><th>{{ __('Items') }}</th><th class="erp-table-actions-col">{{ __('Actions') }}</th></tr></x-slot>
        <x-slot name="body">@forelse ($priceLists as $priceList)<tr x-show="rowVisible(@js(strtolower($priceList->name.' '.$priceList->currency.' '.$priceList->status)))"><td class="font-medium">{{ $priceList->name }}</td><td>{{ $priceList->currency }}</td><td>{{ $priceList->effective_date?->toDateString() ?? __('-') }}</td><td><x-admin.status-badge :variant="$priceList->status === 'active' ? 'success' : 'neutral'">{{ str($priceList->status)->headline() }}</x-admin.status-badge></td><td class="tabular-nums">{{ $priceList->items_count }}</td><td class="erp-table-actions-col"><x-admin.table-row-actions>@if (auth()->user()?->can('catalogue.edit'))<x-admin.table-row-action :href="route('admin.inventory.catalogue.price-lists.edit', $priceList)">{{ __('Edit') }}</x-admin.table-row-action>@endif @if (auth()->user()?->can('catalogue.delete'))<x-admin.table-row-action method="DELETE" :action="route('admin.inventory.catalogue.price-lists.destroy', $priceList)" variant="danger" :confirm="__('Remove this price list?')">{{ __('Remove') }}</x-admin.table-row-action>@endif</x-admin.table-row-actions></td></tr>@empty<tr><td colspan="6"><x-admin.empty-state icon="tag" :title="__('No price lists yet')" /></td></tr>@endforelse</x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$priceLists" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
