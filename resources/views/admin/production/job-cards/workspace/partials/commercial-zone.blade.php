@php
    use App\Support\Navigation\WorkspaceEmbed;

    $tabData = $tabData ?? [];
    $dispatchSummary = $dispatchSummary ?? null;
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();

    $customer = $tabData['customer'] ?? null;
    $salesOrder = $tabData['sales_order'] ?? null;
    $quotation = $tabData['quotation'] ?? null;
    $artwork = $tabData['artwork'] ?? null;

    $deliveryNote = $dispatchSummary['summary'] ?? null;
    $hasDeliveryNote = (bool) ($dispatchSummary['has_delivery_note'] ?? false);

    $badges = [];

    if ($jobCard->salesOrder || $salesOrder) {
        $badges[] = [
            'theme' => 'materials',
            'label' => __('Sales order'),
            'value' => $salesOrder['number'] ?? $jobCard->salesOrder?->order_number,
            'url' => $jobCard->salesOrder ? route('admin.sales-orders.show', $jobCard->salesOrder) : null,
        ];
    }

    if ($jobCard->quotation || $quotation) {
        $badges[] = [
            'theme' => 'commercial',
            'label' => __('Quote'),
            'value' => $quotation['number'] ?? $jobCard->quotation?->quotation_number,
            'url' => ($jobCard->quotation && auth()->user()?->can('view', $jobCard->quotation))
                ? route('admin.quotations.show', $jobCard->quotation)
                : null,
        ];
    }

    if ($jobCard->artworkRequest || $artwork) {
        $badges[] = [
            'theme' => 'qc',
            'label' => __('Artwork'),
            'value' => $artwork['number'] ?? $jobCard->artworkRequest?->request_number,
            'url' => ($jobCard->artworkRequest && auth()->user()?->can('view', $jobCard->artworkRequest))
                ? route('admin.artwork.show', $jobCard->artworkRequest)
                : route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']),
        ];
    }

    $badges[] = [
        'theme' => 'dispatch',
        'label' => __('Dispatch'),
        'value' => ($hasDeliveryNote && $deliveryNote)
            ? ($deliveryNote['delivery_note_number'] ?? '—')
            : __('Open dispatch'),
        'url' => ($hasDeliveryNote && $deliveryNote)
            ? ($deliveryNote['show_url'] ?? '#')
            : route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']),
    ];
@endphp

<x-admin.job-module-card theme="commercial" :title="__('Commercial')" icon="currency-dollar" compact aria-label="{{ __('Commercial') }}">
    <div class="space-y-2">
        @foreach ($badges as $badge)
            @if ($badge['url'])
                <a
                    href="{{ $badge['url'] }}"
                    class="job-360-commercial-badge job-360-commercial-badge--{{ $badge['theme'] }}"
                    @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
                >
                    <span class="job-360-commercial-badge__label">{{ $badge['label'] }}</span>
                    <span class="job-360-commercial-badge__value">{{ $badge['value'] }}</span>
                </a>
            @else
                <div class="job-360-commercial-badge job-360-commercial-badge--{{ $badge['theme'] }}">
                    <span class="job-360-commercial-badge__label">{{ $badge['label'] }}</span>
                    <span class="job-360-commercial-badge__value">{{ $badge['value'] }}</span>
                </div>
            @endif
        @endforeach

        @if ($customer)
            <div class="job-360-commercial-badge job-360-commercial-badge--slate">
                <span class="job-360-commercial-badge__label">{{ __('Customer') }}</span>
                <span class="job-360-commercial-badge__value">{{ $customer['name'] }}</span>
            </div>
        @endif
    </div>
</x-admin.job-module-card>
