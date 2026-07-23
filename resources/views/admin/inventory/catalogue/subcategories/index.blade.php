<x-admin-layout :title="__('Subcategories')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'catalogue'])], ['label' => __('Subcategories')]]">
    <x-admin.page-header :title="__('Subcategories')"><x-slot name="actions">@if (auth()->user()?->can('catalogue.create'))<a href="{{ route('admin.inventory.catalogue.subcategories.create') }}" class="erp-btn-primary">{{ __('New Subcategory') }}</a>@endif</x-slot></x-admin.page-header>
    <x-admin.data-table
        :search-placeholder="__('Search subcategories...')"
        export-route="admin.inventory.exports"
        :export-route-params="['listing' => 'subcategories']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="catalogue-subcategories"
    >
        <x-slot name="head"><tr><th>{{ __('Subcategory') }}</th><th>{{ __('Category') }}</th><th>{{ __('Items') }}</th><th class="erp-table-actions-col">{{ __('Actions') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($subcategories as $subcategory)
                <tr x-show="rowVisible(@js(strtolower($subcategory->code.' '.$subcategory->name.' '.($subcategory->category?->name ?? ''))))">
                    <td><div class="font-medium">{{ $subcategory->name }}</div><div class="font-mono text-[11px] text-slate-500">{{ $subcategory->code }}</div></td>
                    <td>{{ $subcategory->category?->name }}</td>
                    <td class="tabular-nums">{{ $subcategory->items_count }}</td>
                    <td class="erp-table-actions-col"><x-admin.table-row-actions>@if (auth()->user()?->can('catalogue.edit'))<x-admin.table-row-action :href="route('admin.inventory.catalogue.subcategories.edit', $subcategory)">{{ __('Edit') }}</x-admin.table-row-action>@endif @if (auth()->user()?->can('catalogue.delete'))<x-admin.table-row-action method="DELETE" :action="route('admin.inventory.catalogue.subcategories.destroy', $subcategory)" variant="danger" :confirm="__('Remove this subcategory?')">{{ __('Remove') }}</x-admin.table-row-action>@endif</x-admin.table-row-actions></td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state icon="template" :title="__('No subcategories yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$subcategories" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
