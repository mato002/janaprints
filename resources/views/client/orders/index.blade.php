<x-layouts.client :title="__('Orders')" :heading="__('Orders')">
    <div class="client-table-wrap">
        <table class="client-table client-table--cards">
            <thead>
                <tr>
                    <th>{{ __('Order') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Expected completion') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td data-label="{{ __('Order') }}">{{ $order->order_number }}</td>
                        <td data-label="{{ __('Date') }}">{{ $order->order_date?->format('M j, Y') }}</td>
                        <td data-label="{{ __('Expected completion') }}">{{ $order->tracking_summary['expected_completion']?->format('M j, Y') ?: '—' }}</td>
                        <td data-label="{{ __('Amount') }}">KES {{ number_format((float) $order->total_amount, 0) }}</td>
                        <td data-label="{{ __('Status') }}">{{ $order->tracking_summary['status_label'] }}</td>
                        <td data-label="{{ __('Action') }}"><a href="{{ route('client.orders.show', $order) }}" class="client-link">{{ __('Open') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="client-empty">{{ __('No orders yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</x-layouts.client>
