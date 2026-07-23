@php
    $rail = $workspace['sidebar'];
    $score = $rail['lead_score'] ?? $workspace['lead_score'];
    $next = $workspace['next_action'];
@endphp

<div class="space-y-4">
    <x-admin.record-workspace.rail-card :title="__('Opportunity')">
        <x-slot:actions>
            <span class="rw-score rw-score--{{ $score['variant'] }}">{{ $score['label'] }}</span>
        </x-slot:actions>

        <div class="mb-4 space-y-3">
            <x-admin.record-workspace.meter
                :label="__('Opportunity score')"
                :value="$score['points']"
                :display="$score['points'].'%'"
                :hint="$score['hint']"
            />

            <x-admin.record-workspace.meter
                :label="__('Win probability')"
                :value="$rail['probability_value']"
                :display="$rail['probability']"
            />
        </div>

        <dl class="rw-rail-list">
            <div>
                <dt>{{ __('Status') }}</dt>
                <dd><x-admin.status-badge :variant="$rail['status']->badgeVariant()">{{ $rail['status']->workspaceLabel() }}</x-admin.status-badge></dd>
            </div>
            <div>
                <dt>{{ __('Priority') }}</dt>
                <dd>{{ $rail['priority'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Assigned') }}</dt>
                <dd>{{ $rail['assigned_to'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Expected value') }}</dt>
                <dd>{{ $rail['expected_value'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Follow-up') }}</dt>
                <dd>{{ $rail['follow_up_at'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Next action') }}</dt>
                <dd class="font-semibold text-sky-700">{{ $next['label'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Last activity') }}</dt>
                <dd>{{ $rail['last_activity']?->diffForHumans() ?? '—' }}</dd>
            </div>
        </dl>
    </x-admin.record-workspace.rail-card>

    <x-admin.record-workspace.rail-card :title="__('Customer contact')">
        <dl class="rw-rail-list">
            <div>
                <dt>{{ __('Phone') }}</dt>
                <dd><a href="tel:{{ preg_replace('/\s+/', '', $rail['phone']) }}" class="rw-hero-snapshot__link">{{ $rail['phone'] }}</a></dd>
            </div>
            <div>
                <dt>{{ __('Email') }}</dt>
                <dd><a href="mailto:{{ $rail['email'] }}" class="rw-hero-snapshot__link truncate">{{ $rail['email'] }}</a></dd>
            </div>
            <div>
                <dt>{{ __('Artwork files') }}</dt>
                <dd>{{ $rail['artwork_count'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Source') }}</dt>
                <dd>{{ $rail['source'] }}</dd>
            </div>
            @if ($rail['deadline'])
                <div>
                    <dt>{{ __('Deadline') }}</dt>
                    <dd>{{ $rail['deadline'] }}</dd>
                </div>
            @endif
        </dl>
    </x-admin.record-workspace.rail-card>

    <x-admin.record-workspace.rail-card :title="__('Conversion progress')">
        <ol class="space-y-2" role="list">
            @foreach ($workspace['conversion'] as $step)
                <li class="flex items-center justify-between gap-2 text-sm">
                    <span @class([
                        'font-medium',
                        'text-emerald-700' => $step['linked'],
                        'text-slate-500' => ! $step['linked'],
                    ])>
                        @if ($step['linked']) ✓ @else ○ @endif
                        {{ $step['label'] }}
                    </span>
                    @if ($step['linked'] && ! empty($step['url']))
                        <a href="{{ $step['url'] }}" class="text-xs font-semibold text-erp-accent hover:underline" data-turbo-frame="erp-main">{{ __('Open') }}</a>
                    @elseif (! $step['linked'] && ! empty($step['url']))
                        <a href="{{ $step['url'] }}" class="text-xs font-semibold text-slate-500 hover:text-erp-accent" data-turbo-frame="erp-main">{{ __('Start') }}</a>
                    @endif
                </li>
            @endforeach
        </ol>
    </x-admin.record-workspace.rail-card>
</div>
