<x-admin-layout :title="__('Sales orders')" :breadcrumbs="[['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')], ['label' => __('List')]]">
    <x-admin.page-header :title="__('Sales orders')">
        @can('create', App\Models\Sales\SalesOrder::class)
            <a href="{{ route('admin.sales-orders.create') }}" class="erp-btn-primary">{{ __('New from quotation') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Order') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Quotation') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->customer?->company_name }}</td>
                            <td>{{ $order->quotation?->quotation_number }}</td>
                            <td><span class="erp-badge">{{ str_replace('_', ' ', $order->status->value) }}</span></td>
                            <td>{{ number_format($order->total_amount, 2) }}</td>
                            <td><a href="{{ route('admin.sales-orders.show', $order) }}" class="text-indigo-600">{{ __('View') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-slate-500 py-4">{{ __('No sales orders yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </x-admin.card>
</x-admin-layout>
