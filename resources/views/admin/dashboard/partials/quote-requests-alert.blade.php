@php
    $alert = $dashboard['quote_requests_alert'] ?? ['visible' => false, 'count' => 0];
@endphp

@if (! empty($alert['visible']))
    <section
        @class([
            'exec-quote-alert',
            'exec-quote-alert--active' => ($alert['has_action'] ?? false),
            'exec-quote-alert--clear' => ! ($alert['has_action'] ?? false),
        ])
        aria-label="{{ __('New quote requests') }}"
    >
        <div class="exec-quote-alert__main">
            @if ($alert['has_action'] ?? false)
                <span class="exec-quote-alert__pulse" aria-hidden="true"></span>
                <span class="exec-quote-alert__ribbon">{{ __('Action Required') }}</span>
            @endif
            <h2 class="exec-quote-alert__title">{{ $alert['label'] ?? __('New Quote Requests') }}</h2>
            <p class="exec-quote-alert__count">{{ number_format((int) ($alert['count'] ?? 0)) }}</p>
            <p class="exec-quote-alert__subtext">{{ $alert['subtext'] ?? '' }}</p>
        </div>

        @if (! empty($alert['route']))
            <a
                href="{{ $alert['route'] }}"
                data-turbo-frame="erp-main"
                class="exec-quote-alert__cta"
            >
                {{ $alert['cta'] ?? __('Review Requests') }}
            </a>
        @endif
    </section>
@endif
