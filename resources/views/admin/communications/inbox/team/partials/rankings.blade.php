<section class="exec-panel exec-team-cc__rankings" aria-label="{{ __('Top performers') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Top Performers') }}</h2>
        <span class="exec-panel__meta">{{ __('By volume handled') }}</span>
    </div>

    @if ($rankings->isEmpty())
        <x-admin.exec-empty-state :title="__('No rankings yet')" compact />
    @else
        <ol class="exec-team-cc__leaderboard" role="list">
            @foreach ($rankings->take(3) as $index => $row)
                <li class="exec-team-cc__leaderboard-item">
                    <span class="exec-team-cc__leaderboard-rank">{{ $index + 1 }}</span>
                    <div class="exec-team-cc__leaderboard-body">
                        <span class="exec-team-cc__leaderboard-name">{{ $row['user'] }}</span>
                        <div class="exec-team-cc__leaderboard-meta">
                            <span>{{ __('Handled') }}: <strong>{{ $row['conversations_handled'] }}</strong></span>
                            <span>{{ __('Avg response') }}: <strong>{{ $row['avg_response_minutes'] ?? '—' }}</strong></span>
                            <span>{{ __('Resolution') }}: <strong>{{ $row['avg_resolution_minutes'] ?? ($row['escalation_rate'] > 0 ? (100 - $row['escalation_rate']).'%' : '—') }}</strong></span>
                        </div>
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</section>
