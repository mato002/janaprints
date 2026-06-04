<section class="exec-panel exec-panel--activity exec-inbox-cc__activity h-full" aria-label="{{ __('Executive activity feed') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Executive Activity Feed') }}</h2>
        <span class="exec-panel__meta">{{ __('Newest first') }}</span>
    </div>
    <p class="mb-2 text-[10px] leading-snug text-slate-500">{{ __('Replies, assignments, escalations, and customer interactions across monitored threads.') }}</p>

    <div class="exec-activity-feed exec-activity-feed--prominent exec-inbox-cc__feed">
        @forelse ($activityFeed as $event)
            @php
                $dotClass = match ($event['tone']) {
                    'danger' => 'exec-inbox-cc__feed-dot--danger',
                    'warning' => 'exec-inbox-cc__feed-dot--warning',
                    default => 'exec-inbox-cc__feed-dot--default',
                };
            @endphp
            <article class="exec-inbox-cc__feed-item">
                <span class="exec-inbox-cc__feed-dot {{ $dotClass }}" aria-hidden="true"></span>
                <div class="exec-inbox-cc__feed-body">
                    <div class="exec-inbox-cc__feed-head">
                        <span class="exec-inbox-cc__feed-type">{{ $event['title'] }}</span>
                        <time class="exec-inbox-cc__feed-time" datetime="{{ $event['at']->toIso8601String() }}">{{ $event['at']->diffForHumans() }}</time>
                    </div>
                    <a href="{{ $event['href'] }}" class="exec-inbox-cc__feed-link" data-turbo-frame="erp-main">{{ $event['body'] }}</a>
                    @if ($event['meta'])
                        <p class="exec-inbox-cc__feed-meta">{{ $event['meta'] }}</p>
                    @endif
                </div>
            </article>
        @empty
            <x-admin.exec-empty-state
                :title="__('No recent inbox events')"
                :description="__('Escalations, assignments, and waiting threads will appear here.')"
                compact
            />
        @endforelse
    </div>
</section>
