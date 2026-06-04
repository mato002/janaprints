<section class="exec-panel exec-panel--insights exec-team-cc__insights" aria-label="{{ __('Team insights') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Team Insights') }}</h2>
    </div>

    <dl class="exec-team-cc__insights-list">
        <div class="exec-team-cc__insight-row">
            <dt>{{ __('Most active user') }}</dt>
            <dd>{{ $mostActive['user'] ?? __('—') }}</dd>
        </div>
        <div class="exec-team-cc__insight-row">
            <dt>{{ __('Fastest responder') }}</dt>
            <dd>
                @if (($fastestResponder['avg_response_minutes'] ?? null) !== null)
                    {{ $fastestResponder['user'] }} · {{ $fastestResponder['avg_response_minutes'] }}m
                @elseif ($fastestResponder)
                    {{ $fastestResponder['user'] }} · {{ __('Lowest escalation') }} ({{ $fastestResponder['escalation_rate'] }}%)
                @else
                    {{ __('—') }}
                @endif
            </dd>
        </div>
        <div class="exec-team-cc__insight-row">
            <dt>{{ __('Highest resolution rate') }}</dt>
            <dd>
                @if (($highestResolution['avg_resolution_minutes'] ?? null) !== null)
                    {{ $highestResolution['user'] }} · {{ $highestResolution['avg_resolution_minutes'] }}m
                @elseif ($highestResolution)
                    {{ $highestResolution['user'] }} · {{ max(0, 100 - $highestResolution['escalation_rate']) }}%
                @else
                    {{ __('—') }}
                @endif
            </dd>
        </div>
        <div class="exec-team-cc__insight-row">
            <dt>{{ __('Most escalations') }}</dt>
            <dd>
                @if ($hasEscalationSignal)
                    {{ $mostEscalations['user'] }} · {{ $mostEscalations['escalation_rate'] }}%
                @else
                    {{ __('None') }}
                @endif
            </dd>
        </div>
        <div class="exec-team-cc__insight-row exec-team-cc__insight-row--highlight">
            <dt>{{ __('Team utilization') }}</dt>
            <dd class="tabular-nums">{{ $teamUtilization }}%</dd>
        </div>
    </dl>
</section>
