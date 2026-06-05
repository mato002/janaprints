<section class="crm-360__card">
    <h2 class="crm-360__card-title">{{ __('Request Timeline') }}</h2>

    <ul class="crm-360__timeline" role="list">
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
