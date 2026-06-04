@php
    $summaryMetrics = [
        ['label' => __('Active Conversations'), 'value' => $totals['active'], 'tone' => 'default'],
        ['label' => __('Escalated Threads'), 'value' => $totals['escalated'], 'tone' => $totals['escalated'] > 0 ? 'danger' : 'muted'],
        ['label' => __('Unassigned Threads'), 'value' => $totals['unassigned'], 'tone' => $totals['unassigned'] > 0 ? 'warning' : 'muted'],
        ['label' => __('Avg First Response'), 'value' => $teamAvgFirstResponse, 'tone' => 'default'],
        ['label' => __('Avg Resolution Time'), 'value' => $teamAvgResolution, 'tone' => 'default'],
    ];
@endphp

<section class="exec-panel exec-team-cc__summary" aria-label="{{ __('Team operations summary') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Team Operations Summary') }}</h2>
        <span class="exec-panel__meta">{{ __(':members team members', ['members' => $teamMembers->count()]) }}</span>
    </div>
    <div class="exec-team-cc__summary-grid">
        @foreach ($summaryMetrics as $metric)
            @php
                $valueClass = match ($metric['tone']) {
                    'danger' => 'text-red-600',
                    'warning' => 'text-amber-600',
                    default => 'text-erp-primary',
                };
            @endphp
            <div class="exec-team-cc__summary-metric">
                <span class="exec-team-cc__summary-label">{{ $metric['label'] }}</span>
                <span class="exec-team-cc__summary-value {{ $valueClass }}">{{ $metric['value'] }}</span>
            </div>
        @endforeach
    </div>
</section>
