@php
    $rail = $workspace['sidebar'];
    $next = $workspace['next_action'];
@endphp

<aside class="qr-360__rail">
    <section class="qr-360__card qr-360__card--rail">
        <div class="qr-360__rail-head">
            <h2 class="qr-360__card-title">{{ __('Opportunity') }}</h2>
            <span class="qr-360__score qr-360__score--{{ $workspace['lead_score']['variant'] }}">{{ $workspace['lead_score']['label'] }}</span>
        </div>

        <dl class="qr-360__rail-list">
            <div>
                <dt>{{ __('Status') }}</dt>
                <dd><x-admin.status-badge :variant="$rail['status']->badgeVariant()">{{ $rail['status']->workspaceLabel() }}</x-admin.status-badge></dd>
            </div>
            <div>
                <dt>{{ __('Priority') }}</dt>
                <dd>{{ $rail['priority'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Assigned User') }}</dt>
                <dd>{{ $rail['assigned_to'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Expected Value') }}</dt>
                <dd>{{ $rail['expected_value'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Probability') }}</dt>
                <dd>{{ $rail['probability'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Follow-Up Date') }}</dt>
                <dd>{{ $rail['follow_up_at'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Next Action') }}</dt>
                <dd class="qr-360__rail-highlight">{{ $next['label'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Last Activity') }}</dt>
                <dd>{{ $rail['last_activity']?->diffForHumans() ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    <section class="qr-360__card qr-360__card--rail">
        <h2 class="qr-360__card-title">{{ __('Customer Contact') }}</h2>
        <dl class="qr-360__rail-list">
            <div>
                <dt>{{ __('Phone') }}</dt>
                <dd><a href="tel:{{ preg_replace('/\s+/', '', $rail['phone']) }}" class="qr-360__field-link">{{ $rail['phone'] }}</a></dd>
            </div>
            <div>
                <dt>{{ __('Email') }}</dt>
                <dd><a href="mailto:{{ $rail['email'] }}" class="qr-360__field-link">{{ $rail['email'] }}</a></dd>
            </div>
            <div>
                <dt>{{ __('Artwork Files') }}</dt>
                <dd>{{ $rail['artwork_count'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Submitted') }}</dt>
                <dd>{{ $rail['submitted_at']->format('d M Y') }}</dd>
            </div>
        </dl>
    </section>
</aside>
