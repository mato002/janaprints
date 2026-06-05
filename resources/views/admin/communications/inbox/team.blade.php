@php
    $byUser = collect($report['by_user']);
    $totals = $report['totals'];

    $capacityBase = max(10, (int) $byUser->max('assigned_load'));

    $teamMembers = $byUser->map(function (array $row) use ($capacityBase) {
        $capacityPercent = min(100, (int) round(($row['assigned_load'] / $capacityBase) * 100));
        $escalatedEstimate = $row['assigned_load'] > 0
            ? (int) round($row['assigned_load'] * ($row['escalation_rate'] / 100))
            : 0;

        return array_merge($row, [
            'capacity_percent' => $capacityPercent,
            'escalated_count' => $escalatedEstimate,
            'status' => match (true) {
                $capacityPercent >= 80 => 'overloaded',
                $row['assigned_load'] === 0 => 'idle',
                default => 'active',
            },
        ]);
    })->sortByDesc('assigned_load')->values();

    $rankings = $byUser->sortByDesc('conversations_handled')->values();

    $responseValues = $byUser->pluck('avg_response_minutes')->filter(fn ($v) => $v !== null);
    $resolutionValues = $byUser->pluck('avg_resolution_minutes')->filter(fn ($v) => $v !== null);

    $teamAvgFirstResponse = $responseValues->isNotEmpty()
        ? round($responseValues->avg(), 1).'m'
        : '—';

    $teamAvgResolution = $resolutionValues->isNotEmpty()
        ? round($resolutionValues->avg(), 1).'m'
        : '—';

    $teamUtilization = $teamMembers->isNotEmpty()
        ? (int) round($teamMembers->avg('capacity_percent'))
        : 0;

    $mostActive = $rankings->first();
    $fastestResponder = $byUser
        ->filter(fn ($r) => $r['avg_response_minutes'] !== null)
        ->sortBy('avg_response_minutes')
        ->first();
    $highestResolution = $byUser
        ->filter(fn ($r) => $r['avg_resolution_minutes'] !== null)
        ->sortBy('avg_resolution_minutes')
        ->first();

    if (! $fastestResponder && $byUser->isNotEmpty()) {
        $fastestResponder = $byUser->sortBy('escalation_rate')->first();
    }

    if (! $highestResolution && $byUser->isNotEmpty()) {
        $highestResolution = $byUser->sortByDesc(fn ($r) => $r['conversations_handled'] > 0 ? (100 - $r['escalation_rate']) : -1)->first();
    }

    $mostEscalations = $byUser->sortByDesc('escalation_rate')->first();
    $hasEscalationSignal = ($mostEscalations['escalation_rate'] ?? 0) > 0;

    $inboxUnassignedUrl = route('admin.communications.inbox.index', ['view' => 'unassigned']);
@endphp

<x-admin-layout :title="__('Inbox team performance')" :breadcrumbs="[['label' => __('Inbox'), 'url' => route('admin.communications.inbox.index')], ['label' => __('Team')]]">
    <div class="exec-team-cc">
        <header class="exec-dashboard__header">
            <div>
                <div class="comms-action-bar mb-2">
                    <x-admin.crm-btn variant="primary" size="sm" :href="route('admin.communications.inbox.index')" data-turbo-frame="erp-main">{{ __('Open shared inbox') }}</x-admin.crm-btn>
                    @can('executive', App\Models\Communications\Inbox\CommunicationConversation::class)
                        <x-admin.crm-btn variant="outline" size="sm" :href="route('admin.communications.inbox.executive')" data-turbo-frame="erp-main">{{ __('CEO view') }}</x-admin.crm-btn>
                    @endcan
                </div>
                <h1 class="exec-dashboard__title">{{ __('Team Operations Command Center') }}</h1>
                <p class="exec-dashboard__context">{{ __('Workload, capacity, and performance at a glance — built for inbox managers.') }}</p>
            </div>
            <span class="exec-live-badge">
                <span class="exec-live-badge__dot" aria-hidden="true"></span>
                {{ __('Live team ops') }}
            </span>
        </header>

        @include('admin.communications.inbox.team.partials.summary')

        <div class="exec-team-cc__main grid grid-cols-1 gap-3 xl:grid-cols-12">
            <div class="exec-team-cc__primary xl:col-span-8">
                @include('admin.communications.inbox.team.partials.workload-board')
            </div>
            <aside class="exec-team-cc__rail space-y-3 xl:col-span-4">
                @include('admin.communications.inbox.team.partials.rankings')
                @include('admin.communications.inbox.team.partials.insights')
            </aside>
        </div>

        <div class="exec-team-cc__bottom grid grid-cols-1 gap-3 lg:grid-cols-2">
            @include('admin.communications.inbox.team.partials.unassigned')
            @include('admin.communications.inbox.team.partials.capacity')
        </div>
    </div>
</x-admin-layout>
