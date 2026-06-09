@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Store Operations'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'store-operations'])],
        ['label' => __('Reorder Configuration')],
    ];
@endphp
<x-admin-layout :title="__('Reorder Configuration')" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="__('Warehouse reorder configuration')" :description="__('Per-warehouse min/max levels, safety stock, and reorder quantities.')" />

    @can('create', App\Models\Inventory\InventoryItemWarehouseReorderSetting::class)
        <x-admin.card class="mb-6">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Add / update rule') }}</h3>
            <form method="POST" action="{{ route('admin.inventory.reorder-settings.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-3 lg:grid-cols-6">
                @csrf
                <select name="warehouse_id" class="erp-input text-sm" required>
                    <option value="">{{ __('Warehouse') }}</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="inventory_item_id" class="erp-input text-sm" placeholder="{{ __('Item ID') }}" required>
                <input type="number" step="0.001" name="min_level" class="erp-input text-sm" placeholder="{{ __('Min level') }}" required>
                <input type="number" step="0.001" name="max_level" class="erp-input text-sm" placeholder="{{ __('Max level') }}">
                <input type="number" step="0.001" name="reorder_quantity" class="erp-input text-sm" placeholder="{{ __('Reorder qty') }}" required>
                <input type="number" step="0.001" name="safety_stock" class="erp-input text-sm" placeholder="{{ __('Safety stock') }}" required>
                <button type="submit" class="erp-btn-primary text-sm md:col-span-3 lg:col-span-6">{{ __('Save configuration') }}</button>
            </form>
        </x-admin.card>
    @endcan

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
