@if (! empty($tabData['restricted']))
    <x-admin.empty-state :title="__('Access restricted')" :description="__('You need sales order view permission to see this tab.')" />
@else
    @php($orders = $tabData['orders'])
    <x-admin.data-table :searchable="false" :exportable="false" :filterable="false">
        <x-slot:head>
            <tr>
                <th>{{ __('Order number') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Production status') }}</th>
                <th class="text-end">{{ __('Total') }}</th>
            </tr>
        </x-slot:head>
        <x-slot:body>
            @forelse ($orders as $order)
                <tr>
                    <td>
                        <a href="{{ route('admin.sales-orders.show', $order) }}" class="font-medium text-erp-accent hover:text-erp-accent-hover" data-turbo-frame="erp-main">{{ $order->order_number }}</a>
                    </td>
                    <td>{{ $order->order_date?->format('Y-m-d') }}</td>
                    <td><x-admin.enum-status-badge :status="$order->status->value" /></td>
                    <td>
                        @if ($order->jobCard)
                            <x-admin.enum-status-badge :status="$order->jobCard->status->value" />
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="text-end tabular-nums">{{ number_format($order->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-500 py-6">{{ __('No sales orders for this customer.') }}</td></tr>
            @endforelse
        </x-slot:body>
        @if ($orders->hasPages())
            <x-slot:footer>
                <x-admin.table-pagination :paginator="$orders" />
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endif
