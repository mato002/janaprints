@php
    use App\Support\Navigation\WorkspaceEmbed;

    $tabData = $tabData ?? [];
    $dispatchSummary = $dispatchSummary ?? null;
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();

    $customer = $tabData['customer'] ?? ($jobCard->customer ? [
        'name' => $jobCard->customer->company_name,
        'code' => $jobCard->customer->customer_code,
    ] : null);
    $salesOrder = isset($tabData['sales_order']['number'])
        ? $tabData['sales_order']
        : ($jobCard->salesOrder ? ['number' => $jobCard->salesOrder->order_number] : null);
    $quotation = isset($tabData['quotation']['number'])
        ? $tabData['quotation']
        : ($jobCard->quotation ? ['number' => $jobCard->quotation->quotation_number] : null);
    $artwork = isset($tabData['artwork']['number'])
        ? $tabData['artwork']
        : ($jobCard->artworkRequest ? ['number' => $jobCard->artworkRequest->request_number] : null);

    $deliveryNote = $dispatchSummary['summary'] ?? null;
    $hasDeliveryNote = (bool) ($dispatchSummary['has_delivery_note'] ?? false);

    $commercialUrl = route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'commercial']);
@endphp

<div class="job-360-commercial-chips" aria-label="{{ __('Commercial links') }}">
    @if ($jobCard->salesOrder || $salesOrder)
        @if ($jobCard->salesOrder && auth()->user()?->can('view', $jobCard->salesOrder))
            <a
                href="{{ route('admin.sales-orders.show', $jobCard->salesOrder) }}"
                class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link"
                title="{{ __('Sales order') }}"
                @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
            >
                <span class="job-360-commercial-chips__abbr">{{ __('SO') }}</span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value">{{ $salesOrder['number'] ?? $jobCard->salesOrder->order_number }}</span>
            </a>
        @else
            <span class="job-360-commercial-chips__chip" title="{{ __('Sales order') }}">
                <span class="job-360-commercial-chips__abbr">{{ __('SO') }}</span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value">{{ $salesOrder['number'] ?? $jobCard->salesOrder?->order_number }}</span>
            </span>
        @endif
    @else
        <span class="job-360-commercial-chips__chip job-360-commercial-chips__chip--muted" title="{{ __('Sales order') }}">
            <span class="job-360-commercial-chips__abbr">{{ __('SO') }}</span>
            <span class="mes-chip-status" aria-hidden="true">○</span>
        </span>
    @endif

    @if ($jobCard->quotation || $quotation)
        @if ($jobCard->quotation && auth()->user()?->can('view', $jobCard->quotation))
            <a href="{{ route('admin.quotations.show', $jobCard->quotation) }}" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach title="{{ __('Quotation') }}">
                <span class="job-360-commercial-chips__abbr">{{ __('QT') }}</span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value">{{ $quotation['number'] ?? $jobCard->quotation->quotation_number }}</span>
            </a>
        @else
            <span class="job-360-commercial-chips__chip" title="{{ __('Quotation') }}">
                <span class="job-360-commercial-chips__abbr">{{ __('QT') }}</span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value">{{ $quotation['number'] ?? $jobCard->quotation?->quotation_number }}</span>
            </span>
        @endif
    @else
        <span class="job-360-commercial-chips__chip job-360-commercial-chips__chip--muted" title="{{ __('Quotation') }}">
            <span class="job-360-commercial-chips__abbr">{{ __('QT') }}</span>
            <span class="mes-chip-status" aria-hidden="true">○</span>
        </span>
    @endif

    @if ($jobCard->artworkRequest || $artwork)
        @if ($jobCard->artworkRequest && auth()->user()?->can('view', $jobCard->artworkRequest))
            <a href="{{ route('admin.artwork.show', $jobCard->artworkRequest) }}" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach title="{{ __('Artwork') }}">
                <span class="job-360-commercial-chips__abbr">{{ __('AW') }}</span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value">{{ $artwork['number'] ?? $jobCard->artworkRequest->request_number }}</span>
            </a>
        @else
            <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']) }}" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach title="{{ __('Artwork') }}">
                <span class="job-360-commercial-chips__abbr">{{ __('AW') }}</span>
                <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
                <span class="job-360-commercial-chips__value">{{ $artwork['number'] ?? $jobCard->artworkRequest?->request_number }}</span>
            </a>
        @endif
    @else
        <span class="job-360-commercial-chips__chip job-360-commercial-chips__chip--muted" title="{{ __('Artwork') }}">
            <span class="job-360-commercial-chips__abbr">{{ __('AW') }}</span>
            <span class="mes-chip-status" aria-hidden="true">○</span>
        </span>
    @endif

    @if ($hasDeliveryNote && $deliveryNote)
        <a href="{{ $deliveryNote['show_url'] ?? '#' }}" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link font-mono" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach title="{{ __('Delivery note') }}">
            <span class="job-360-commercial-chips__abbr">{{ __('DN') }}</span>
            <span class="mes-chip-status mes-chip-status--ok" aria-hidden="true">✓</span>
            <span class="job-360-commercial-chips__value">{{ $deliveryNote['delivery_note_number'] ?? '—' }}</span>
        </a>
    @else
        <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']) }}" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--muted" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach title="{{ __('Delivery note') }}">
            <span class="job-360-commercial-chips__abbr">{{ __('DN') }}</span>
            <span class="mes-chip-status" aria-hidden="true">○</span>
        </a>
    @endif

    <a href="{{ $commercialUrl }}" class="job-360-commercial-chips__chip job-360-commercial-chips__chip--link" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach title="{{ __('Commercial tab') }}">
        <span class="job-360-commercial-chips__abbr">{{ __('$') }}</span>
        <span class="job-360-commercial-chips__value">{{ __('Cost') }}</span>
    </a>

    @if ($customer)
        <span class="job-360-commercial-chips__chip job-360-commercial-chips__chip--customer" title="{{ $customer['code'] ?? '' }}">
            <span class="job-360-commercial-chips__abbr">{{ __('Cust') }}</span>
            <span class="job-360-commercial-chips__value">{{ $customer['name'] }}</span>
        </span>
    @endif
</div>
