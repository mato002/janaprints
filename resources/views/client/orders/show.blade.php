<x-layouts.client :title="$order->order_number" :heading="$order->order_number" :subtitle="__('Order tracking')">
    <div class="client-detail">
        <div class="client-detail__meta">
            <p><strong>{{ __('Order date') }}:</strong> {{ $order->order_date?->format('F j, Y') }}</p>
            <p><strong>{{ __('Expected completion') }}:</strong> {{ $tracking['expected_completion']?->format('F j, Y') ?: '—' }}</p>
            <p><strong>{{ __('Status') }}:</strong> {{ $tracking['status_label'] }}</p>
            <p><strong>{{ __('Total') }}:</strong> KES {{ number_format((float) $order->total_amount, 0) }}</p>
            @if ($order->quotation)
                <p><strong>{{ __('Quote reference') }}:</strong> {{ $order->quotation->quotation_number }}</p>
            @endif
        </div>

        <div class="client-tracking mb-6">
            <h3 class="client-panel__title mb-3">{{ __('Progress') }}</h3>
            <ol class="client-tracking__steps">
                @foreach ($tracking['stages'] as $stage)
                    <li @class([
                        'client-tracking__step',
                        'is-complete' => $stage['state'] === 'complete',
                        'is-current' => $stage['state'] === 'current',
                    ])>
                        <span class="client-tracking__label">{{ $stage['label'] }}</span>
                    </li>
                @endforeach
            </ol>
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
