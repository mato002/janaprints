<section class="crm-360__card">
    <h2 class="crm-360__card-title">{{ __('Unified timeline') }}</h2>
    <p class="mb-4 text-[11px] text-slate-500">{{ __('Quotes, orders, artwork, payments, communications, and activities in one ledger.') }}</p>

    <ul class="crm-360__timeline" role="list">
        @forelse ($unifiedTimeline->take(40) as $event)
            @php
                $badgeClass = match ($event['kind']) {
                    'communication' => 'crm-360__badge--comm',
                    'payment' => 'crm-360__badge--payment',
                    'quote', 'order', 'artwork' => 'crm-360__badge--commercial',
                    'activity' => 'crm-360__badge--activity',
                    default => 'crm-360__badge--default',
                };
            @endphp
            <li class="crm-360__timeline-item">
                <span class="crm-360__timeline-dot" aria-hidden="true"></span>
                <div class="crm-360__timeline-body">
                    <div class="crm-360__timeline-head">
                        <span class="crm-360__badge {{ $badgeClass }}">{{ $event['badge'] }}</span>
                        <time class="crm-360__timeline-date">{{ $event['at']?->format('M j, Y') }}</time>
                    </div>
                    @if ($event['url'])
                        <a href="{{ $event['url'] }}" class="crm-360__timeline-title" data-turbo-frame="erp-main">{{ $event['title'] }}</a>
                    @else
                        <span class="crm-360__timeline-title">{{ $event['title'] }}</span>
                    @endif
                    <p class="crm-360__timeline-meta">{{ $event['body'] }} · {{ $event['at']?->diffForHumans() }}</p>
                </div>
            </li>
        @empty
            <li class="crm-360__empty-inline py-8 text-center">{{ __('No timeline events yet') }}</li>
        @endforelse
    </ul>
</section>
