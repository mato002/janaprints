<section class="exec-panel exec-team-cc__capacity-panel" aria-label="{{ __('Team capacity') }}">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title">{{ __('Team Capacity') }}</h2>
        <span class="exec-panel__meta">{{ __('Relative load · base :n', ['n' => $capacityBase]) }}</span>
    </div>

    @if ($teamMembers->isEmpty())
        <x-admin.exec-empty-state :title="__('No capacity data')" compact />
    @else
        <ul class="exec-team-cc__capacity-list" role="list">
            @foreach ($teamMembers as $member)
                @php
                    $capacityTone = match (true) {
                        $member['capacity_percent'] >= 80 => 'danger',
                        $member['capacity_percent'] <= 20 => 'success',
                        default => 'default',
                    };
                    $barVariant = match ($capacityTone) {
                        'danger' => 'exec-progress__bar--danger',
                        'success' => 'exec-progress__bar--success',
                        default => '',
                    };
                @endphp
                <li class="exec-team-cc__capacity-row exec-team-cc__capacity-row--{{ $capacityTone }}">
                    <div class="exec-team-cc__capacity-label">
                        <span class="exec-team-cc__capacity-name">{{ $member['user'] }}</span>
                        <span class="exec-team-cc__capacity-pct tabular-nums">{{ $member['capacity_percent'] }}%</span>
                    </div>
                    <div class="exec-progress__track exec-team-cc__capacity-track" role="progressbar" aria-valuenow="{{ $member['capacity_percent'] }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="exec-progress__bar {{ $barVariant }}" style="width: {{ max($member['capacity_percent'], $member['assigned_load'] > 0 ? 4 : 0) }}%"></div>
                    </div>
                    @if ($member['status'] === 'overloaded')
                        <span class="exec-team-cc__capacity-flag">{{ __('Overloaded — consider redistribution') }}</span>
                    @elseif ($member['status'] === 'idle')
                        <span class="exec-team-cc__capacity-flag exec-team-cc__capacity-flag--idle">{{ __('Underutilized — available for assignments') }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</section>
