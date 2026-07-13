@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Store Operations'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'store-operations'])],
        ['label' => __('Reorder Configuration')],
    ];
@endphp
<x-admin-layout :title="__('Reorder Configuration')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header
        :title="__('Warehouse reorder configuration')"
        :description="__('Per-warehouse min/max levels, safety stock, and reorder quantities.')"
    >
        <x-slot name="actions">
            @can('create', App\Models\Inventory\InventoryItemWarehouseReorderSetting::class)
                <a href="{{ route('admin.inventory.reorder-settings.create') }}" class="erp-btn-primary" data-erp-modal-open>
                    {{ __('Add reorder rule') }}
                </a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Warehouse') }}</th>
                <th>{{ __('Item') }}</th>
                <th>{{ __('Min') }}</th>
                <th>{{ __('Max') }}</th>
                <th>{{ __('Reorder qty') }}</th>
                <th>{{ __('Safety stock') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($settings as $row)
                <tr>
                    <td>{{ $row->warehouse?->name }}</td>
                    <td>{{ $row->inventoryItem?->sku }} — {{ $row->inventoryItem?->item_name }}</td>
                    <td class="tabular-nums">{{ number_format((float) $row->min_level, 3) }}</td>
                    <td class="tabular-nums">{{ $row->max_level !== null ? number_format((float) $row->max_level, 3) : '—' }}</td>
                    <td class="tabular-nums">{{ number_format((float) $row->reorder_quantity, 3) }}</td>
                    <td class="tabular-nums">{{ number_format((float) $row->safety_stock, 3) }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state :title="__('No warehouse reorder rules')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$settings" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
