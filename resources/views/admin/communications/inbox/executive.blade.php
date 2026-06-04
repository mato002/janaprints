@php
    use App\Enums\InboxSlaStatus;

    $inboxRoute = fn ($conversationId) => route('admin.communications.inbox.index', ['conversation' => $conversationId]);

    $conversationPools = collect([
        $stats['longest_waiting'],
        $stats['recent_escalated'],
        $stats['recent_unassigned'],
        $stats['vip_waiting'],
        $stats['high_value_waiting'],
        $stats['recent_complaints'],
    ])->flatten(1)->unique('id');

    $channelKeys = [
        'whatsapp' => ['label' => __('WhatsApp')],
        'sms' => ['label' => __('SMS')],
        'email' => ['label' => __('Email')],
    ];
    $channelTotal = $conversationPools->filter(fn ($c) => filled($c->last_channel))->count();
    $channelMix = collect($channelKeys)->map(function ($meta, $key) use ($conversationPools, $channelTotal) {
        $count = $conversationPools->filter(fn ($c) => (string) $c->last_channel === $key)->count();
        $pct = $channelTotal > 0 ? (int) round(($count / $channelTotal) * 100) : 0;

        return array_merge($meta, ['key' => $key, 'count' => $count, 'percent' => $pct]);
    });

    $priorityThreads = collect()
        ->merge($stats['vip_waiting'])
        ->merge($stats['high_value_waiting'])
        ->merge($stats['recent_complaints'])
        ->unique('id')
        ->sortByDesc(fn ($c) => $c->last_activity_at?->timestamp ?? 0)
        ->take(8);

    $overdueThreads = $stats['longest_waiting']
        ->filter(fn ($c) => $c->sla_status === InboxSlaStatus::Red)
        ->take(8);

    $assigneeLoads = $conversationPools
        ->filter(fn ($c) => $c->assignee)
        ->groupBy('assigned_user_id')
        ->map(fn ($group) => [
            'name' => $group->first()->assignee->name,
            'count' => $group->count(),
        ])
        ->sortByDesc('count')
        ->values()
        ->take(6);

    $activityFeed = collect();

    foreach ($stats['recent_escalated'] as $conv) {
        $activityFeed->push([
            'at' => $conv->escalated_at ?? $conv->last_activity_at,
            'type' => 'escalation',
            'tone' => 'danger',
            'title' => __('Thread escalated'),
            'body' => $conv->display_name ?? $conv->conversation_code,
            'meta' => $conv->assignee?->name,
            'href' => $inboxRoute($conv->id),
        ]);
    }

    foreach ($stats['recent_unassigned'] as $conv) {
        $activityFeed->push([
            'at' => $conv->last_activity_at,
            'type' => 'assignment',
            'tone' => 'warning',
            'title' => __('Awaiting assignment'),
            'body' => $conv->display_name ?? $conv->conversation_code,
            'meta' => __('Unassigned'),
            'href' => $inboxRoute($conv->id),
        ]);
    }

    foreach ($stats['longest_waiting'] as $conv) {
        $activityFeed->push([
            'at' => $conv->waiting_since ?? $conv->last_activity_at,
            'type' => 'waiting',
            'tone' => 'warning',
            'title' => __('Customer waiting'),
            'body' => $conv->display_name ?? $conv->conversation_code,
            'meta' => $conv->waiting_since?->diffForHumans(),
            'href' => $inboxRoute($conv->id),
        ]);
    }

    foreach ($stats['recent_complaints'] as $conv) {
        $activityFeed->push([
            'at' => $conv->escalated_at ?? $conv->last_activity_at,
            'type' => 'complaint',
            'tone' => 'danger',
            'title' => __('Complaint / escalation signal'),
            'body' => $conv->display_name ?? $conv->conversation_code,
            'meta' => $conv->assignee?->name ?? __('Unassigned'),
            'href' => $inboxRoute($conv->id),
        ]);
    }

    $activityFeed = $activityFeed
        ->filter(fn ($item) => $item['at'] !== null)
        ->sortByDesc(fn ($item) => $item['at']->timestamp)
        ->take(14)
        ->values();
@endphp

<x-admin-layout :title="__('CEO Inbox')" :breadcrumbs="[['label' => __('Inbox'), 'url' => route('admin.communications.inbox.index')], ['label' => __('CEO view')]]">
    <div class="exec-inbox-cc">
        <header class="exec-dashboard__header">
            <div>
                <p class="mb-1">
                    <a href="{{ route('admin.communications.inbox.index') }}" class="text-[11px] font-medium text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('Open inbox') }}</a>
                </p>
                <h1 class="exec-dashboard__title">{{ __('Executive Communication Command Center') }}</h1>
                <p class="exec-dashboard__context">{{ __('Real-time intelligence across customer threads, SLA posture, and team capacity.') }}</p>
            </div>
            <span class="exec-live-badge">
                <span class="exec-live-badge__dot" aria-hidden="true"></span>
                {{ __('Live inbox') }}
            </span>
        </header>

        <div class="exec-inbox-cc__metrics-row">
            @include('admin.communications.inbox.executive.partials.health-panel')
            @include('admin.communications.inbox.executive.partials.performance-panel')
        </div>

        <div class="exec-inbox-cc__main grid grid-cols-1 gap-3 xl:grid-cols-12">
            <div class="exec-inbox-cc__primary space-y-3 xl:col-span-8">
                @include('admin.communications.inbox.executive.partials.attention-center')
                <div class="exec-inbox-cc__bottom grid grid-cols-1 gap-3 lg:grid-cols-2">
                    @include('admin.communications.inbox.executive.partials.channel-distribution')
                    @include('admin.communications.inbox.executive.partials.team-workload')
                </div>
            </div>
            <aside class="exec-inbox-cc__rail xl:col-span-4">
                @include('admin.communications.inbox.executive.partials.activity-feed')
            </aside>
        </div>
    </div>
</x-admin-layout>
