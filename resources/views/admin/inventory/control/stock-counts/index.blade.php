@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Stock Count')],
    ];
@endphp
<x-admin-layout :title="__('Stock Count')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('Stock counts')">
        @can('create', App\Models\Inventory\StockCount::class)
            <a href="{{ route('admin.inventory.stock-counts.create') }}" class="erp-btn-primary">{{ __('New count') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search counts…')"
        export-route="admin.inventory.exports"
        :export-route-params="['listing' => 'stock-counts']"
        :export-query="request()->query()"
        :format-in-path="true"
        export-filename="stock-counts"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Count') }}</th>
                <th scope="col">{{ __('Warehouse') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Date') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($counts as $count)
                <tr x-show="rowVisible(@js(strtolower($count->count_number.' '.$count->warehouse?->name.' '.$count->status->value)))">
                    <td class="font-medium">{{ $count->count_number }}</td>
                    <td>{{ $count->warehouse?->name }}</td>
                    <td>{{ ucfirst($count->count_type->value) }}</td>
                    <td>{{ $count->count_date->format('Y-m-d') }}</td>
                    <td><x-admin.enum-status-badge :status="$count->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.inventory.stock-counts.show', $count)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $count)
                                <x-admin.table-row-action :href="route('admin.inventory.stock-counts.worksheet', $count)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                            <x-admin.table-row-action :href="route('admin.inventory.stock-counts.worksheet', $count)">{{ __('Print Worksheet') }}</x-admin.table-row-action>
                            @can('submit', $count)
                                <x-admin.table-row-action method="POST" :action="route('admin.inventory.stock-counts.submit', $count)">{{ __('Submit') }}</x-admin.table-row-action>
                            @endcan
                            @can('approve', $count)
                                <x-admin.table-row-action method="POST" :action="route('admin.inventory.stock-counts.approve', $count)">{{ __('Approve') }}</x-admin.table-row-action>
                            @endcan
                            @can('post', $count)
                                <x-admin.table-row-action method="POST" :action="route('admin.inventory.stock-counts.post', $count)">{{ __('Post Variance') }}</x-admin.table-row-action>
                            @endcan
                            @can('cancel', $count)
                                <x-admin.table-row-action method="POST" :action="route('admin.inventory.stock-counts.cancel', $count)">{{ __('Cancel') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="clipboard-list" :title="__('No stock counts yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$counts" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
