<x-admin-layout :title="$order->po_number" :breadcrumbs="[['label' => __('Procurement')], ['label' => __('Purchase Orders'), 'url' => route('admin.procurement.orders.index')], ['label' => $order->po_number]]">
    <x-admin.page-header :title="$order->po_number" :description="$order->vendor?->vendor_name">
        <x-slot name="actions">
            @can('update', $order)<a href="{{ route('admin.procurement.orders.edit', $order) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>@endcan
            @can('submit', $order)<form method="POST" action="{{ route('admin.procurement.orders.submit', $order) }}">@csrf<button class="erp-btn-primary">{{ __('Submit') }}</button></form>@endcan
            @can('approve', $order)<form method="POST" action="{{ route('admin.procurement.orders.approve', $order) }}">@csrf<button class="erp-btn-primary">{{ __('Approve') }}</button></form>@endcan
            @can('send', $order)<form method="POST" action="{{ route('admin.procurement.orders.send', $order) }}">@csrf<button class="erp-btn-secondary">{{ __('Mark sent') }}</button></form>@endcan
            @can('receive', $order)<a href="{{ route('admin.procurement.orders.receive.create', $order) }}" class="erp-btn-primary">{{ __('Receive goods') }}</a>@endcan
        </x-slot>
    </x-admin.page-header>
    <x-admin.card>
        <div class="mb-4 flex flex-wrap gap-4 text-sm"><span><strong>{{ __('Status') }}:</strong> {{ str($order->status->value)->headline() }}</span><span><strong>{{ __('Total') }}:</strong> {{ number_format($order->total_amount, 2) }}</span></div>
        <table class="erp-table text-sm">
            <thead><tr><th>{{ __('Description') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Received') }}</th><th>{{ __('Unit cost') }}</th><th>{{ __('Total') }}</th></tr></thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ $item->quantity_received }}</td><td>{{ number_format($item->unit_cost, 2) }}</td><td>{{ number_format($item->line_total, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </x-admin.card>
</x-admin-layout>
