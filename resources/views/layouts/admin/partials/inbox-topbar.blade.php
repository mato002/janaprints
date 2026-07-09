@php
    $inboxUnreadSummaryUrl = \Illuminate\Support\Facades\Route::has('admin.communications.inbox.unread-summary')
        ? route('admin.communications.inbox.unread-summary')
        : null;
@endphp

@if (! empty($inboxTopbar['visible']) && ! empty($inboxTopbar['route']))
    <a
        href="{{ $inboxTopbar['route'] }}"
        data-turbo-frame="erp-main"
        class="erp-quote-topbar-btn"
        title="{{ $inboxTopbar['label'] }}"
        data-inbox-topbar-link
        @if ($inboxUnreadSummaryUrl) data-inbox-unread-summary-url="{{ $inboxUnreadSummaryUrl }}" @endif
    >
        <span>{{ $inboxTopbar['label'] }}</span>
        <span
            class="erp-quote-topbar-btn__badge"
            data-inbox-topbar-badge
            @if (($inboxTopbar['count'] ?? 0) <= 0) hidden @endif
        >{{ $inboxTopbar['count'] ?? 0 }}</span>
    </a>
@endif
