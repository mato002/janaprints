@if (! ($embeddedInDesk ?? false))
    <x-admin.page-header :title="__('Sales orders')">
        @can('create', App\Models\Sales\SalesOrder::class)
            <x-admin.form-modal-link :href="route('admin.sales-orders.create')">
                {{ __('New sales order') }}
            </x-admin.form-modal-link>
        @endcan
    </x-admin.page-header>
@else
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Sales orders') }}</h2>
            <p class="text-xs text-slate-600">{{ __('Confirmed and in-progress orders.') }}</p>
        </div>
        @can('create', App\Models\Sales\SalesOrder::class)
            <x-admin.form-modal-link :href="route('admin.sales-orders.create')">
                {{ __('New sales order') }}
            </x-admin.form-modal-link>
        @endcan
    </div>
@endif

<x-admin.data-table
    :search-placeholder="__('Search sales orders…')"
    export-filename="sales-orders"
    :chips="[
        ['id' => 'all', 'label' => __('All')],
        ['id' => 'draft', 'label' => __('Draft')],
        ['id' => 'confirmed', 'label' => __('Confirmed')],
        ['id' => 'in_production', 'label' => __('In Production')],
        ['id' => 'completed', 'label' => __('Completed')],
    ]"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Order') }}</th>
            <th scope="col">{{ __('Customer') }}</th>
            <th scope="col" class="hidden lg:table-cell">{{ __('Quotation') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col">{{ __('Total') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($orders as $order)
            @php
                $search = strtolower($order->order_number.' '.($order->customer?->company_name ?? '').' '.($order->quotation?->quotation_number ?? '').' '.$order->status->value);
                $chip = strtolower($order->status->value);
            @endphp
            <tr x-show="rowVisible(@js($search), @js($chip))">
                <td class="font-medium">
                    <a
                        href="{{ route('admin.sales-orders.show', [$order, 'from' => 'sales-desk']) }}"
                        class="text-erp-accent hover:underline"
                        data-turbo-frame="erp-main"
                        data-turbo-action="advance"
                    >{{ $order->order_number }}</a>
                </td>
                <td>{{ $order->customer?->company_name ?? '—' }}</td>
                <td class="hidden lg:table-cell">{{ $order->quotation?->quotation_number ?? '—' }}</td>
                <td><x-admin.enum-status-badge :status="$order->status->value" /></td>
                <td class="tabular-nums">{{ number_format($order->total_amount, 2) }}</td>
                <td class="erp-table-actions-col">
                    @include('admin.sales.orders.partials.row-actions', ['order' => $order])
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-admin.empty-state icon="clipboard-list" :title="__('No sales orders yet')" /></td></tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$orders" /></x-slot>
</x-admin.data-table>
