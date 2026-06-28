<x-admin-layout :title="__('Sales orders')" :breadcrumbs="[['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')], ['label' => __('List')]]">
    <x-admin.page-header :title="__('Sales orders')">
        @can('create', App\Models\Sales\SalesOrder::class)
            <x-admin.form-modal-link :href="route('admin.sales-orders.create')">
                {{ __('New from quotation') }}
            </x-admin.form-modal-link>
        @endcan
    </x-admin.page-header>

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
                    <td class="font-medium">{{ $order->order_number }}</td>
                    <td>{{ $order->customer?->company_name ?? '—' }}</td>
                    <td class="hidden lg:table-cell">{{ $order->quotation?->quotation_number ?? '—' }}</td>
                    <td><x-admin.enum-status-badge :status="$order->status->value" /></td>
                    <td class="tabular-nums">{{ number_format($order->total_amount, 2) }}</td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.sales-orders.show', $order)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $order)
                                <x-admin.table-row-action :href="route('admin.sales-orders.edit', $order)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="clipboard-list" :title="__('No sales orders yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$orders" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
