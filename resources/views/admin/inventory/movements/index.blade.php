<x-admin-layout :title="__('Movements')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Inventory'), 'url' => route('admin.inventory.dashboard')], ['label' => __('Stock Movements')]]">
    <x-admin.page-header :title="__('Inventory movements')" :description="__('Audit trail — source of stock truth.')" />

    <x-admin.data-table :search-placeholder="__('Search movements…')" export-filename="inventory-movements">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Date') }}</th>
                <th scope="col">{{ __('Item') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Warehouse') }}</th>
                <th scope="col">{{ __('Type') }}</th>
                <th scope="col">{{ __('Qty') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($movements as $m)
                <tr x-show="rowVisible(@js(strtolower($m->movement_date->format('Y-m-d').' '.($m->item?->sku ?? '').' '.($m->warehouse?->name ?? '').' '.$m->movement_type->value)))">
                    <td>{{ $m->movement_date->format('Y-m-d') }}</td>
                    <td>{{ $m->item?->sku ?? '—' }}</td>
                    <td class="hidden md:table-cell">{{ $m->warehouse?->name ?? '—' }}</td>
                    <td><x-admin.enum-status-badge :status="$m->movement_type->value" /></td>
                    <td class="tabular-nums">{{ $m->quantity }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="switch-horizontal" :title="__('No movements recorded')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$movements" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
