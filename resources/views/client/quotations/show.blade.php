<x-layouts.client :title="$quotation->quotation_number" :heading="$quotation->quotation_number" :subtitle="__('Quote details')">
    <div class="client-detail">
        <div class="client-detail__meta">
            <p><strong>{{ __('Date') }}:</strong> {{ $quotation->quotation_date?->format('F j, Y') }}</p>
            <p><strong>{{ __('Valid until') }}:</strong> {{ $quotation->valid_until?->format('F j, Y') ?: '—' }}</p>
            <p><strong>{{ __('Status') }}:</strong> @include('client.partials.status-badge', ['status' => $quotation->status])</p>
            <p><strong>{{ __('Total') }}:</strong> KES {{ number_format((float) $quotation->total_amount, 0) }}</p>
        </div>

        <div class="client-actions">
            <x-documents.pdf-download-button
                :url="route('client.quotations.pdf', $quotation)"
                :filename="$quotation->quotation_number"
                class="client-btn client-btn--secondary"
            />
        </div>

        @if ($quotation->items->isNotEmpty())
            <div class="client-table-wrap">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Qty') }}</th>
                            <th>{{ __('Unit price') }}</th>
                            <th>{{ __('Line total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotation->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td>{{ number_format((float) $item->quantity, 0) }}</td>
                                <td>KES {{ number_format((float) $item->unit_price, 0) }}</td>
                                <td>KES {{ number_format((float) $item->line_total, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($canRespond)
            <div class="client-review-box">
                <h3 class="client-panel__title">{{ __('Your decision') }}</h3>
                <form method="POST" action="{{ route('client.quotations.accept', $quotation) }}" class="inline">
                    @csrf
                    <button type="submit" class="client-btn">{{ __('Accept quote') }}</button>
                </form>
                <form method="POST" action="{{ route('client.quotations.reject', $quotation) }}" class="client-review-form">
                    @csrf
                    <label for="reason" class="client-label">{{ __('Decline reason (optional)') }}</label>
                    <textarea id="reason" name="reason" rows="3" class="client-input">{{ old('reason') }}</textarea>
                    <button type="submit" class="client-btn client-btn--danger">{{ __('Decline quote') }}</button>
                </form>
            </div>
        @endif
    </div>
</x-layouts.client>
