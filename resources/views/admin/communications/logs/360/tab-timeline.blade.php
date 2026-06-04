<section class="comm-log-360__card">
    <h2 class="comm-log-360__card-title">{{ __('Delivery timeline') }}</h2>
    <ul class="comm-log-360__timeline" role="list">
        <li class="comm-log-360__timeline-item">
            <span class="comm-log-360__timeline-dot comm-log-360__timeline-dot--default" aria-hidden="true"></span>
            <div class="comm-log-360__timeline-body">
                <p class="comm-log-360__timeline-title">{{ __('Created') }}</p>
                <p class="comm-log-360__timeline-meta">{{ $log->created_at?->format('d M Y • H:i') }}</p>
            </div>
        </li>
        @forelse ($timelineEvents as $event)
            @php
                $eventTone = str_contains(strtolower((string) $event->event), 'fail')
                    ? 'danger'
                    : (str_contains(strtolower((string) $event->event), 'deliver') ? 'success' : 'default');
            @endphp
            <li class="comm-log-360__timeline-item">
                <span class="comm-log-360__timeline-dot comm-log-360__timeline-dot--{{ $eventTone }}" aria-hidden="true"></span>
                <div class="comm-log-360__timeline-body">
                    <p class="comm-log-360__timeline-title">{{ ucfirst(str_replace('_', ' ', $event->event)) }}</p>
                    @if ($event->status_snapshot)
                        <p class="comm-log-360__timeline-sub">{{ $event->status_snapshot }}</p>
                    @endif
                    <p class="comm-log-360__timeline-meta">{{ $event->created_at?->format('d M Y • H:i') }}</p>
                </div>
            </li>
        @empty
            @if ($log->sent_at)
                <li class="comm-log-360__timeline-item">
                    <span class="comm-log-360__timeline-dot comm-log-360__timeline-dot--success" aria-hidden="true"></span>
                    <div class="comm-log-360__timeline-body">
                        <p class="comm-log-360__timeline-title">{{ __('Sent') }}</p>
                        <p class="comm-log-360__timeline-meta">{{ $log->sent_at->format('d M Y • H:i') }}</p>
                    </div>
                </li>
            @endif
            @if ($log->delivered_at)
                <li class="comm-log-360__timeline-item">
                    <span class="comm-log-360__timeline-dot comm-log-360__timeline-dot--success" aria-hidden="true"></span>
                    <div class="comm-log-360__timeline-body">
                        <p class="comm-log-360__timeline-title">{{ __('Delivered') }}</p>
                        <p class="comm-log-360__timeline-meta">{{ $log->delivered_at->format('d M Y • H:i') }}</p>
                    </div>
                </li>
            @endif
        @endforelse
    </ul>
</section>
