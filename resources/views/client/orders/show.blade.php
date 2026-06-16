<x-layouts.client :title="$order->order_number" :heading="$order->order_number" :subtitle="__('Order tracking')">
    <div class="client-detail">
        <div class="client-detail__meta">
            <p><strong>{{ __('Order date') }}:</strong> {{ $order->order_date?->format('F j, Y') }}</p>
            <p><strong>{{ __('Required by') }}:</strong> {{ $order->required_date?->format('F j, Y') ?: '—' }}</p>
            <p><strong>{{ __('Status') }}:</strong> @include('client.partials.status-badge', ['status' => $order->status])</p>
            <p><strong>{{ __('Total') }}:</strong> KES {{ number_format((float) $order->total_amount, 0) }}</p>
            @if ($order->quotation)
                <p><strong>{{ __('Quote reference') }}:</strong> {{ $order->quotation->quotation_number }}</p>
            @endif
        </div>

        @if ($order->items->isNotEmpty())
            <div class="client-table-wrap">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Qty') }}</th>
                            <th>{{ __('Line total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td>{{ number_format((float) $item->quantity, 0) }}</td>
                                <td>KES {{ number_format((float) $item->line_total, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layouts.client>
