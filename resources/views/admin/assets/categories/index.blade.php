<x-admin-layout
    :title="__('Asset categories')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Categories')],
    ]"
>
    <x-admin.page-header :title="__('Asset Categories')" :description="__('Asset classification and policies.')">
        @can('create', \App\Models\Assets\AssetCategory::class)
            <x-slot name="actions">
                <a href="{{ route('admin.assets.categories.create') }}" class="erp-btn-primary">{{ __('New category') }}</a>
            </x-slot>
        @endcan
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search categories…')"
        export-filename="asset-categories"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Name') }}</th>
                <th scope="col">{{ __('Code') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Useful life') }}</th>
                <th scope="col">{{ __('Assets') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($categories as $category)
                @php
                    $search = strtolower($category->name.' '.($category->code ?? '').' '.($category->asset_type?->value ?? ''));
                @endphp
                <tr x-show="rowVisible(@js($search))">
                    <td class="font-medium">{{ $category->name }}</td>
                    <td>{{ $category->code ?? '—' }}</td>
                    <td>{{ $category->asset_type?->label() ?? '—' }}</td>
                    <td>{{ $category->useful_life_years ?? ceil($category->useful_life_months / 12) }} {{ __('years') }}</td>
                    <td>{{ $category->assets_count }}</td>
                    <td>
                        @if ($category->is_active)
                            <x-admin.status-badge variant="success">{{ __('Active') }}</x-admin.status-badge>
                        @else
                            <x-admin.status-badge variant="neutral">{{ __('Inactive') }}</x-admin.status-badge>
                        @endif
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @can('update', $category)
                                <x-admin.table-row-action :href="route('admin.assets.categories.edit', $category)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                            @can('archive', $category)
                                <x-admin.table-row-action method="POST" :action="route('admin.assets.categories.archive', $category)" variant="danger" :confirm="__('Archive this category?')">{{ __('Archive') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-admin.empty-state icon="clipboard-list" :title="__('No categories found')" :description="__('Create a category to classify assets and set useful life.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
