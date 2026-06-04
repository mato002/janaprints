<section class="exec-panel exec-team-cc__workload-panel" aria-label="{{ __('Team workload board') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Team Workload Board') }}</h2>
        <span class="exec-panel__meta">{{ __('Capacity vs assigned load') }}</span>
    </div>

    @if ($teamMembers->isEmpty())
        <x-admin.exec-empty-state
            :title="__('No team members')"
            :description="__('Add users to this company to track inbox workload.')"
            compact
        />
    @else
        <div class="exec-team-cc__workload-grid">
            @foreach ($teamMembers as $member)
                @php
                    $statusLabel = match ($member['status']) {
                        'overloaded' => __('Overloaded'),
                        'idle' => __('Available'),
                        default => __('Active'),
                    };
                    $statusClass = match ($member['status']) {
                        'overloaded' => 'exec-team-cc__status--danger',
                        'idle' => 'exec-team-cc__status--muted',
                        default => 'exec-team-cc__status--success',
                    };
                    $barClass = match ($member['status']) {
                        'overloaded' => 'exec-progress__bar--danger',
                        'idle' => 'exec-team-cc__bar--idle',
                        default => 'exec-progress__bar',
                    };
                @endphp
                <article class="exec-team-cc__member-card exec-team-cc__member-card--{{ $member['status'] }}">
                    <div class="exec-team-cc__member-head">
                        <h3 class="exec-team-cc__member-name">{{ $member['user'] }}</h3>
                        <span class="exec-team-cc__status {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>

                    <div class="exec-team-cc__member-bar-block">
                        <div class="exec-team-cc__member-bar-label">
                            <span>{{ __('Conversations') }}</span>
                            <span class="tabular-nums">{{ $member['assigned_load'] }} / {{ $capacityBase }}</span>
                        </div>
                        <div class="exec-progress__track exec-team-cc__member-track" role="progressbar" aria-valuenow="{{ $member['capacity_percent'] }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="exec-progress__bar {{ $barClass }}" style="width: {{ max($member['capacity_percent'], $member['assigned_load'] > 0 ? 6 : 0) }}%"></div>
                        </div>
                    </div>

                    <dl class="exec-team-cc__member-stats">
                        <div>
                            <dt>{{ __('Handled') }}</dt>
                            <dd>{{ $member['conversations_handled'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Assigned load') }}</dt>
                            <dd>{{ $member['assigned_load'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Escalated') }}</dt>
                            <dd>{{ $member['escalated_count'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Capacity used') }}</dt>
                            <dd class="{{ $member['capacity_percent'] >= 80 ? 'text-red-600' : ($member['capacity_percent'] <= 20 ? 'text-emerald-600' : '') }}">{{ $member['capacity_percent'] }}%</dd>
                        </div>
                    </dl>
                </article>
            @endforeach
        </div>
    @endif
</section>
