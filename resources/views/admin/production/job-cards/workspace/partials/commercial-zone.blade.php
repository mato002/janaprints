@php
    $tabData = $tabData ?? [];
    $dispatchSummary = $dispatchSummary ?? null;

    $customer = $tabData['customer'] ?? null;
    $salesOrder = $tabData['sales_order'] ?? null;
    $quotation = $tabData['quotation'] ?? null;
    $artwork = $tabData['artwork'] ?? null;

    $deliveryNote = $dispatchSummary['summary'] ?? null;
    $hasDeliveryNote = (bool) ($dispatchSummary['has_delivery_note'] ?? false);
@endphp

<section class="job-360-zone job-360-zone--commercial" aria-label="{{ __('Commercial') }}">
    <header class="job-360-zone__head">
        <x-admin.icon name="receipt-tax" class="h-5 w-5 text-violet-600" />
        <h2 class="job-360-zone__title">{{ __('Commercial') }}</h2>
    </header>

    <ul class="job-360-commercial-links">
        <li class="job-360-commercial-links__item">
            <span class="job-360-commercial-links__label">{{ __('Sales order') }}</span>
            @if ($jobCard->salesOrder && auth()->user()?->can('view', $jobCard->salesOrder))
                <a href="{{ route('admin.sales-orders.show', $jobCard->salesOrder) }}" class="job-360-commercial-links__value" data-turbo-frame="erp-main">
                    {{ $salesOrder['number'] ?? $jobCard->salesOrder->order_number }}
                </a>
                <span class="job-360-commercial-links__meta">{{ str_replace('_', ' ', $salesOrder['status'] ?? $jobCard->salesOrder->status->value) }}</span>
            @elseif ($salesOrder)
                <span class="job-360-commercial-links__value">{{ $salesOrder['number'] }}</span>
            @else
                <span class="job-360-commercial-links__empty">{{ __('Not linked') }}</span>
            @endif
        </li>

        <li class="job-360-commercial-links__item">
            <span class="job-360-commercial-links__label">{{ __('Quotation') }}</span>
            @if ($jobCard->quotation && auth()->user()?->can('view', $jobCard->quotation))
                <a href="{{ route('admin.quotations.show', $jobCard->quotation) }}" class="job-360-commercial-links__value" data-turbo-frame="erp-main">
                    {{ $quotation['number'] ?? $jobCard->quotation->quotation_number }}
                </a>
            @elseif ($quotation)
                <span class="job-360-commercial-links__value">{{ $quotation['number'] }}</span>
            @else
                <span class="job-360-commercial-links__empty">{{ __('Not linked') }}</span>
            @endif
        </li>

        <li class="job-360-commercial-links__item">
            <span class="job-360-commercial-links__label">{{ __('Artwork') }}</span>
            @if ($jobCard->artworkRequest && auth()->user()?->can('view', $jobCard->artworkRequest))
                <a href="{{ route('admin.artwork.show', $jobCard->artworkRequest) }}" class="job-360-commercial-links__value" data-turbo-frame="erp-main">
                    {{ $artwork['number'] ?? $jobCard->artworkRequest->request_number }}
                </a>
                <span class="job-360-commercial-links__meta">{{ str_replace('_', ' ', $artwork['status'] ?? $jobCard->artworkRequest->status->value) }}</span>
            @elseif ($artwork)
                <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']) }}" class="job-360-commercial-links__value" data-turbo-frame="erp-main">
                    {{ $artwork['number'] }}
                </a>
            @else
                <span class="job-360-commercial-links__empty">{{ __('Not linked') }}</span>
            @endif
        </li>

        <li class="job-360-commercial-links__item">
            <span class="job-360-commercial-links__label">{{ __('Delivery note') }}</span>
            @if ($hasDeliveryNote && $deliveryNote)
                <a href="{{ $deliveryNote['show_url'] ?? '#' }}" class="job-360-commercial-links__value font-mono" data-turbo-frame="erp-main">
                    {{ $deliveryNote['delivery_note_number'] ?? '—' }}
                </a>
            @else
                <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']) }}" class="job-360-commercial-links__value job-360-commercial-links__value--muted" data-turbo-frame="erp-main">
                    {{ __('Open dispatch') }}
                </a>
            @endif
        </li>

        @if ($customer)
            <li class="job-360-commercial-links__item job-360-commercial-links__item--secondary">
                <span class="job-360-commercial-links__label">{{ __('Customer') }}</span>
                <span class="job-360-commercial-links__value">{{ $customer['name'] }}</span>
                <span class="job-360-commercial-links__meta">{{ $customer['code'] ?? '' }}</span>
            </li>
        @endif
    </ul>
</section>
