<x-admin-layout :title="__('Categories')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Catalogue'), 'url' => route('admin.inventory.catalogue.dashboard')], ['label' => __('Categories')]]">
    <x-admin.page-header :title="__('Categories')">
        <x-slot name="actions">
            @if (auth()->user()?->can('catalogue.create'))
                <a href="{{ route('admin.inventory.catalogue.categories.create') }}" class="erp-btn-primary">{{ __('New category') }}</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search categories...')"
        export-route="admin.inventory.exports"
        :export-route-params="['listing' => 'categories']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="catalogue-categories"
    >
        <x-slot name="head">
            <tr>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Default UOM') }}</th>
                <th>{{ __('Reorder') }}</th>
                <th>{{ __('Items') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($categories as $category)
                <tr x-show="rowVisible(@js(strtolower($category->code.' '.$category->name.' '.$category->reorder_behavior)))">
                    <td><div class="font-medium">{{ $category->name }}</div><div class="font-mono text-[11px] text-slate-500">{{ $category->code }}</div></td>
                    <td>{{ $category->defaultUom?->name ?? __('-') }}</td>
                    <td>{{ str($category->reorder_behavior)->headline() }}</td>
                    <td class="tabular-nums">{{ $category->items_count }} / {{ $category->subcategories_count }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @if (auth()->user()?->can('catalogue.edit'))
                                <x-admin.table-row-action :href="route('admin.inventory.catalogue.categories.edit', $category)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endif
                            @if (auth()->user()?->can('catalogue.delete'))
                                <x-admin.table-row-action method="DELETE" :action="route('admin.inventory.catalogue.categories.destroy', $category)" variant="danger" :confirm="__('Remove this category?')">{{ __('Remove') }}</x-admin.table-row-action>
                            @endif
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="folder" :title="__('No categories yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$categories" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
