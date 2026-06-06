@php
    $header = $workspace['header'];
@endphp

<header class="qr-360__header">
    <div class="qr-360__header-top">
        <a href="{{ route('admin.public-quote-requests.index') }}" class="qr-360__back" data-turbo-frame="erp-main">
            ← {{ __('Quote Requests') }}
        </a>
        <div class="qr-360__header-pills">
            <x-admin.status-badge :variant="$header['status_variant']">{{ $header['status_label'] }}</x-admin.status-badge>
            <span class="qr-360__pill qr-360__pill--neutral">{{ $header['priority_label'] }}</span>
            <span class="qr-360__score qr-360__score--{{ $workspace['lead_score']['variant'] }}">{{ $workspace['lead_score']['label'] }}</span>
        </div>
    </div>

    <div class="qr-360__header-body">
        <div class="qr-360__header-id">
            <h1 class="qr-360__ref">{{ $header['reference'] }}</h1>
            <p class="qr-360__header-line">
                <span>{{ $header['customer_name'] }}</span>
                <span class="qr-360__sep">·</span>
                <span>{{ $header['service'] }}</span>
                <span class="qr-360__sep">·</span>
                <span>{{ $header['quantity'] }} {{ __('units') }}</span>
                <span class="qr-360__sep">·</span>
                <span>{{ $header['submitted_at'] }}</span>
            </p>
        </div>
        <dl class="qr-360__header-meta">
            <div>
                <dt>{{ __('Assigned') }}</dt>
                <dd>{{ $header['assigned_to'] }}</dd>
            </div>
            <div>
                <dt>{{ __('Expected Value') }}</dt>
                <dd>{{ $header['expected_value'] }}</dd>
            </div>
        </dl>
    </div>
</header>
