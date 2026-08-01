@if (! ($embeddedInDesk ?? false))
    <x-admin.page-header :title="__('Purchase Orders')">
        <x-slot name="actions">
            @can('create', App\Models\Procurement\PurchaseOrder::class)
                <a href="{{ route('admin.procurement.orders.create') }}" class="erp-btn-primary">{{ __('Create order') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>
@else
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ $registerTitle ?? __('Purchase Orders') }}</h2>
        </div>
        @can('create', App\Models\Procurement\PurchaseOrder::class)
            <a href="{{ route('admin.procurement.orders.create') }}" class="erp-btn-primary text-sm">{{ __('Create order') }}</a>
        @endcan
    </div>
@endif

<x-admin.data-table
    :search-placeholder="__('Search purchase orders…')"
    export-route="admin.procurement.exports"
    :export-route-params="['listing' => 'purchase-orders']"
    :export-query="request()->query()"
    :format-in-path="true"
    export-filename="purchase-orders"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('PO Number') }}</th>
            <th scope="col">{{ __('Vendor') }}</th>
            <th scope="col" class="hidden md:table-cell">{{ __('Date') }}</th>
            <th scope="col">{{ __('Total') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($orders as $order)
            <tr x-show="rowVisible(@js(strtolower($order->po_number.' '.($order->vendor?->vendor_name ?? '').' '.$order->status->value)))">
                <td class="font-mono text-xs">{{ $order->po_number }}</td>
                <td>{{ $order->vendor?->vendor_name ?? '—' }}</td>
                <td class="hidden md:table-cell">{{ $order->order_date?->format('Y-m-d') }}</td>
                <td class="tabular-nums">{{ number_format($order->total_amount, 2) }}</td>
                <td><x-admin.enum-status-badge :status="$order->status->value" /></td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        <x-admin.table-row-action :href="route('admin.procurement.orders.show', $order)">{{ __('View') }}</x-admin.table-row-action>
                        @can('update', $order)
                            <x-admin.table-row-action :href="route('admin.procurement.orders.edit', $order)">{{ __('Edit') }}</x-admin.table-row-action>
                        @endcan
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-admin.empty-state icon="shopping-bag" :title="__('No purchase orders')" /></td></tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$orders" /></x-slot>
</x-admin.data-table>
