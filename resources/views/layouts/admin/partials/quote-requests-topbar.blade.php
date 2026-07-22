@if (! empty($quoteRequestsTopbar['visible']) && ! empty($quoteRequestsTopbar['route']))
    <a
        href="{{ $quoteRequestsTopbar['route'] }}"
        data-turbo-frame="erp-main"
        class="erp-quote-topbar-btn"
        title="{{ $quoteRequestsTopbar['label'] }}"
        aria-label="{{ $quoteRequestsTopbar['label'] }}"
    >
        <x-admin.icon name="document-text" class="erp-quote-topbar-btn__icon h-4 w-4 shrink-0 sm:hidden" />
        <span class="erp-quote-topbar-btn__label hidden sm:inline">{{ $quoteRequestsTopbar['label'] }}</span>
        @if (($quoteRequestsTopbar['count'] ?? 0) > 0)
            <span class="erp-quote-topbar-btn__badge">{{ $quoteRequestsTopbar['count'] }}</span>
        @endif
    </a>
@endif
