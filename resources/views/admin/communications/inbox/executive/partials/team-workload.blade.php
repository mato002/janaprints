<section class="exec-panel exec-inbox-cc__section-panel" aria-label="{{ __('Team workload') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Team Workload') }}</h2>
        <span class="exec-panel__meta">{{ __(':count unassigned', ['count' => $stats['unassigned']]) }}</span>
    </div>

    <div class="exec-inbox-cc__workload-split">
        <div>
            <h3 class="exec-inbox-cc__subhead">{{ __('Conversations per user') }}</h3>
            @if ($assigneeLoads->isEmpty())
                <p class="exec-inbox-cc__thread-empty">{{ __('No assignee load in the current snapshot.') }}</p>
            @else
                <ul class="exec-inbox-cc__workload-bars" role="list">
                    @php $maxLoad = max(1, (int) $assigneeLoads->max('count')); @endphp
                    @foreach ($assigneeLoads as $row)
                        @php $pct = (int) round(($row['count'] / $maxLoad) * 100); @endphp
                        <li class="exec-inbox-cc__workload-bar-row">
                            <span class="exec-inbox-cc__workload-name">{{ $row['name'] }}</span>
                            <div class="exec-progress__track exec-inbox-cc__workload-track">
                                <div class="exec-progress__bar" style="width: {{ $pct }}%"></div>
                            </div>
                            <span class="exec-inbox-cc__workload-count">{{ $row['count'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div>
            <h3 class="exec-inbox-cc__subhead">{{ __('Unassigned conversations') }}</h3>
            <ul class="exec-inbox-cc__thread-list" role="list">
                @forelse ($stats['recent_unassigned'] as $conv)
                    <li>
                        <a href="{{ route('admin.communications.inbox.index', ['conversation' => $conv->id]) }}" class="exec-inbox-cc__thread-row" data-turbo-frame="erp-main">
                            <span class="exec-inbox-cc__thread-name">{{ $conv->display_name ?? $conv->conversation_code }}</span>
                            <span class="exec-inbox-cc__thread-meta">{{ $conv->last_activity_at?->diffForHumans() }}</span>
                        </a>
                    </li>
                @empty
                    <li class="exec-inbox-cc__thread-empty">{{ __('All active threads are assigned.') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    @if ($stats['most_active_customers']->isNotEmpty())
        <details class="exec-intelligence mt-3">
            <summary class="exec-intelligence__summary">
                {{ __('Most active customers') }}
                <span class="exec-intelligence__hint">{{ __('Thread volume') }}</span>
            </summary>
            <div class="exec-intelligence__body">
                <ul class="exec-inbox-cc__thread-list" role="list">
                    @foreach ($stats['most_active_customers'] as $row)
                        <li class="exec-inbox-cc__thread-row exec-inbox-cc__thread-row--static">
                            <span class="exec-inbox-cc__thread-name">{{ $row->customer?->company_name ?? __('Unknown') }}</span>
                            <span class="exec-inbox-cc__thread-meta">{{ $row->thread_count }} {{ __('threads') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </details>
    @endif
</section>
