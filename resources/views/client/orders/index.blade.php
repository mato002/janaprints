<x-layouts.client :title="__('Orders')" :heading="__('Orders')">
    <div class="client-table-wrap">
        <table class="client-table">
            <thead>
                <tr>
                    <th>{{ __('Order') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Required by') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->order_date?->format('M j, Y') }}</td>
                        <td>{{ $order->required_date?->format('M j, Y') ?: '—' }}</td>
                        <td>KES {{ number_format((float) $order->total_amount, 0) }}</td>
                        <td>@include('client.partials.status-badge', ['status' => $order->status])</td>
                        <td><a href="{{ route('client.orders.show', $order) }}" class="client-link">{{ __('Open') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="client-empty">{{ __('No orders yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</x-layouts.client>
