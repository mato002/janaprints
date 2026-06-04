<section class="exec-panel exec-panel--attention exec-team-cc__unassigned" aria-label="{{ __('Unassigned conversations') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Unassigned Conversations') }}</h2>
        <span class="exec-badge exec-badge--{{ $totals['unassigned'] > 0 ? 'warning' : 'muted' }}">{{ $totals['unassigned'] }}</span>
    </div>

    @if ($totals['unassigned'] === 0)
        <x-admin.exec-empty-state
            :title="__('Queue is clear')"
            :description="__('All active conversations have an owner.')"
            compact
        />
    @else
        <article class="exec-team-cc__queue-card">
            <p class="exec-team-cc__queue-count">{{ $totals['unassigned'] }}</p>
            <p class="exec-team-cc__queue-title">{{ trans_choice('conversation needs assignment|conversations need assignment', $totals['unassigned']) }}</p>
            <dl class="exec-team-cc__unassigned-meta exec-team-cc__unassigned-meta--queue">
                <div>
                    <dt>{{ __('Assigned status') }}</dt>
                    <dd class="text-amber-700">{{ __('None') }}</dd>
                </div>
                <div>
                    <dt>{{ __('Waiting') }}</dt>
                    <dd>{{ __('Open inbox for wait times') }}</dd>
                </div>
                <div>
                    <dt>{{ __('Priority') }}</dt>
                    <dd>{{ __('Open inbox for priority') }}</dd>
                </div>
            </dl>
            <a href="{{ $inboxUnassignedUrl }}" class="erp-btn erp-btn--primary erp-btn--sm mt-3" data-turbo-frame="erp-main">
                {{ __('Review & assign in Shared Inbox') }}
            </a>
        </article>
        <p class="mt-2 text-[10px] text-slate-500">{{ __('Customer names and SLA details are shown in the inbox unassigned view.') }}</p>
    @endif
</section>
