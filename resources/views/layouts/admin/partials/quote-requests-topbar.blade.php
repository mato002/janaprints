@if (! empty($quoteRequestsTopbar['visible']) && ! empty($quoteRequestsTopbar['route']))
    <a
        href="{{ $quoteRequestsTopbar['route'] }}"
        data-turbo-frame="erp-main"
        class="erp-quote-topbar-btn"
        title="{{ $quoteRequestsTopbar['label'] }}"
    >
        <span>{{ $quoteRequestsTopbar['label'] }}</span>
        @if (($quoteRequestsTopbar['count'] ?? 0) > 0)
            <span class="erp-quote-topbar-btn__badge">{{ $quoteRequestsTopbar['count'] }}</span>
        @endif
    </a>
@endif
