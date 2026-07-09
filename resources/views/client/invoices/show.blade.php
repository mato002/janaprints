<x-layouts.client :title="$invoice->invoice_number" :heading="$invoice->invoice_number" :subtitle="__('Invoice details')">
    <div class="client-detail">
        <div class="client-detail__meta">
            <p><strong>{{ __('Invoice date') }}:</strong> {{ $invoice->invoice_date?->format('F j, Y') }}</p>
            <p><strong>{{ __('Due date') }}:</strong> {{ $invoice->due_date?->format('F j, Y') ?: '—' }}</p>
            <p><strong>{{ __('Total') }}:</strong> KES {{ number_format((float) $invoice->total_amount, 0) }}</p>
            <p><strong>{{ __('Balance due') }}:</strong> KES {{ number_format((float) $invoice->balance_due, 0) }}</p>
        </div>

        <div class="client-actions">
            <x-documents.pdf-download-button
                :url="route('client.invoices.pdf', $invoice)"
                :filename="$invoice->invoice_number"
                class="client-btn client-btn--secondary"
            />
        </div>

        @if ($invoice->lines->isNotEmpty())
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
                        @foreach ($invoice->lines as $item)
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
