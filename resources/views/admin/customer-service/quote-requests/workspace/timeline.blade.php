<section class="qr-360__card">
    <button
        type="button"
        class="qr-360__collapse-head"
        @click="timelineOpen = ! timelineOpen"
        :aria-expanded="timelineOpen"
    >
        <h2 class="qr-360__card-title">{{ __('Activity Timeline') }}</h2>
        <x-admin.icon name="chevron-down" class="h-4 w-4 transition-transform" ::class="timelineOpen && 'rotate-180'" />
    </button>

    <ul class="crm-360__timeline" role="list" x-show="timelineOpen" x-cloak>
        @foreach ($workspace['timeline'] as $event)
            <li class="crm-360__timeline-item">
                <span class="crm-360__timeline-dot" aria-hidden="true"></span>
                <div class="crm-360__timeline-body">
                    <div class="crm-360__timeline-head">
                        <span class="crm-360__badge crm-360__badge--activity">{{ $event['badge'] }}</span>
                        <time class="crm-360__timeline-date">{{ $event['at']?->format('d M Y, H:i') }}</time>
                    </div>
                    <span class="crm-360__timeline-title">{{ $event['title'] }}</span>
                    <p class="crm-360__timeline-meta">{{ $event['body'] }} · {{ $event['at']?->diffForHumans() }}</p>
                </div>
            </li>
        @endforeach
    </ul>
</section>
