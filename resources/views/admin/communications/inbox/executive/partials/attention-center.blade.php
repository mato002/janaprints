@php
    $attentionColumns = [
        [
            'title' => __('Priority conversations'),
            'badge' => $priorityThreads->count(),
            'tone' => 'critical',
            'items' => $priorityThreads,
            'empty' => __('No VIP or high-value threads need attention.'),
        ],
        [
            'title' => __('Waiting customers'),
            'badge' => $stats['longest_waiting']->count(),
            'tone' => 'warning',
            'items' => $stats['longest_waiting'],
            'empty' => __('No customers are waiting on a reply.'),
            'hint' => fn ($conv) => $conv->waiting_since?->diffForHumans(),
        ],
        [
            'title' => __('Overdue SLA'),
            'badge' => $stats['overdue'],
            'tone' => 'critical',
            'items' => $overdueThreads,
            'empty' => __('All SLAs are within target.'),
            'hint' => fn ($conv) => $conv->sla_status?->label(),
        ],
        [
            'title' => __('Escalated threads'),
            'badge' => $stats['escalated'],
            'tone' => 'critical',
            'items' => $stats['recent_escalated'],
            'empty' => __('No escalated threads right now.'),
            'hint' => fn ($conv) => $conv->escalated_at?->diffForHumans(),
        ],
    ];
@endphp

<section class="exec-panel exec-panel--attention exec-inbox-cc__attention" aria-label="{{ __('Attention center') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Attention Center') }}</h2>
        <span class="exec-attention-ribbon">{{ __('Needs review') }}</span>
    </div>

    <div class="exec-inbox-cc__attention-grid">
        @foreach ($attentionColumns as $column)
            <div class="exec-inbox-cc__attention-col exec-inbox-cc__attention-col--{{ $column['tone'] }}">
                <div class="exec-inbox-cc__attention-col-head">
                    <h3 class="exec-inbox-cc__attention-col-title">{{ $column['title'] }}</h3>
                    <span class="exec-badge exec-badge--{{ $column['tone'] === 'critical' ? 'danger' : 'warning' }}">{{ $column['badge'] }}</span>
                </div>
                <ul class="exec-inbox-cc__thread-list" role="list">
                    @forelse ($column['items'] as $conv)
                        <li>
                            <a href="{{ route('admin.communications.inbox.index', ['conversation' => $conv->id]) }}" class="exec-inbox-cc__thread-row" data-turbo-frame="erp-main">
                                <span class="exec-inbox-cc__thread-name">{{ $conv->display_name ?? $conv->conversation_code }}</span>
                                @if (! empty($column['hint']))
                                    <span class="exec-inbox-cc__thread-meta">{{ $column['hint']($conv) }}</span>
                                @elseif ($conv->assignee)
                                    <span class="exec-inbox-cc__thread-meta">{{ $conv->assignee->name }}</span>
                                @endif
                            </a>
                        </li>
                    @empty
                        <li class="exec-inbox-cc__thread-empty">{{ $column['empty'] }}</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>
</section>
