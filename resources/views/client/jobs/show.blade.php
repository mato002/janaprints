<x-layouts.client :title="$jobCard->job_card_number" :heading="$jobCard->job_card_number" :subtitle="__('Production tracking')">
    <div class="client-detail">
        @if ($tracking)
            <div class="client-detail__meta">
                <p><strong>{{ __('Status') }}:</strong> {{ $tracking['status_label'] }}</p>
                <p><strong>{{ __('Expected completion') }}:</strong> {{ $tracking['expected_completion']?->format('F j, Y') ?: '—' }}</p>
                @if ($jobCard->salesOrder)
                    <p><strong>{{ __('Order') }}:</strong> <a href="{{ route('client.orders.show', $jobCard->salesOrder) }}" class="client-link">{{ $jobCard->salesOrder->order_number }}</a></p>
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
        @endif

        @if ($deliveryNotes->isNotEmpty())
            <section class="client-panel mb-6">
                <h3 class="client-panel__title mb-3">{{ __('Delivery status') }}</h3>
                @foreach ($deliveryNotes as $note)
                    <div class="client-list-item">
                        <span class="client-list-item__primary">{{ $note->delivery_note_number }}</span>
                        <span class="client-list-item__meta">{{ $note->status->label() }}</span>
                    </div>
                @endforeach
            </section>
        @endif

        @if ($jobCard->salesOrder?->invoices?->isNotEmpty())
            <section class="client-panel">
                <h3 class="client-panel__title mb-3">{{ __('Related documents') }}</h3>
                @foreach ($jobCard->salesOrder->invoices as $invoice)
                    <a href="{{ route('client.invoices.pdf', $invoice) }}" class="client-btn client-btn--secondary mb-2 inline-flex" target="_blank" rel="noopener">
                        {{ __('Invoice PDF') }} — {{ $invoice->invoice_number }}
                    </a>
                @endforeach
            </section>
        @endif
    </div>
</x-layouts.client>
