<section class="crm-360__card">
    <h2 class="crm-360__card-title">{{ __('Acquisition timeline') }}</h2>
    <ul class="crm-360__feed" role="list">
        @forelse ($timeline as $event)
            <li class="crm-360__feed-item">
                <div class="crm-360__feed-head">
                    @if ($event['url'])
                        <a href="{{ $event['url'] }}" class="crm-360__feed-title" data-turbo-frame="erp-main">{{ $event['title'] }}</a>
                    @else
                        <span class="crm-360__feed-title">{{ $event['title'] }}</span>
                    @endif
                    <span class="crm-360__pill">{{ $event['badge'] }}</span>
                </div>
                <p class="crm-360__feed-meta">{{ $event['body'] }}</p>
                <time class="crm-360__feed-time">{{ $event['at']?->format('d M Y H:i') }}</time>
            </li>
        @empty
            <li class="crm-360__empty-inline">{{ __('No timeline events yet') }}</li>
        @endforelse
    </ul>
</section>
