@php
    $inboxUnreadSummaryUrl = \Illuminate\Support\Facades\Route::has('admin.communications.inbox.unread-summary')
        ? route('admin.communications.inbox.unread-summary')
        : null;
    $currentView = $filters['view'] ?? 'all';
    $viewChips = [
        'all' => __('All'),
        'open' => __('Open'),
        'my' => __('Assigned'),
        'unassigned' => __('Unassigned'),
        'waiting_customer' => __('Waiting'),
        'closed' => __('Closed'),
    ];
    $extraViews = [
        'unread' => __('Unread'),
        'waiting_internal' => __('Waiting internal'),
        'escalated' => __('Escalated'),
        'overdue' => __('Overdue'),
    ];
    $chipQuery = fn (string $view) => $inboxEmbedUrl(route('admin.communications.inbox.index', array_merge(
        request()->except(['page']),
        array_filter([
            'view' => $view,
            'q' => $filters['q'] ?? null,
            'status' => $filters['status'] ?? null,
            'tag' => $filters['tag'] ?? null,
            'conversation' => $active?->id,
        ], fn ($v) => $v !== null && $v !== '')
    )));
@endphp

<aside
    class="shared-inbox__list-panel flex h-full min-h-0 w-full flex-col overflow-hidden"
    data-inbox-list-panel
    @if ($inboxUnreadSummaryUrl) data-inbox-unread-summary-url="{{ $inboxUnreadSummaryUrl }}" @endif
>
    <div class="shared-inbox__list-header">
        <div x-show="newConvoOpen" x-cloak class="shared-inbox__new-panel">
            @include('admin.communications.inbox.partials.start-conversation', ['compact' => true])
        </div>
        <div x-show="!newConvoOpen" class="text-[11px] text-slate-500">
            <button type="button" class="font-semibold text-indigo-700 hover:underline" @click="newConvoOpen = true">+ {{ __('New conversation') }}</button>
        </div>

        <form method="GET" class="space-y-0" data-turbo-frame="{{ $inboxTurboFrame }}">
            @if ($active)<input type="hidden" name="conversation" value="{{ $active->id }}">@endif
            <input type="hidden" name="view" value="{{ $currentView }}">
            @if (! empty($filters['status']))<input type="hidden" name="status" value="{{ $filters['status'] }}">@endif
            @if (! empty($filters['tag']))<input type="hidden" name="tag" value="{{ $filters['tag'] }}">@endif

            <div class="shared-inbox__search-wrap">
                <svg class="shared-inbox__search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
                <input
                    type="search"
                    name="q"
                    value="{{ $filters['q'] ?? '' }}"
                    class="shared-inbox__search"
                    placeholder="{{ __('Search conversations…') }}"
                >
            </div>
        </form>

        <nav class="shared-inbox__filters" aria-label="{{ __('Conversation filters') }}">
            @foreach ($viewChips as $key => $label)
                <a
                    href="{{ $chipQuery($key) }}"
                    data-turbo-frame="{{ $inboxTurboFrame }}"
                    class="shared-inbox__chip {{ $currentView === $key ? 'shared-inbox__chip--active' : '' }}"
                >{{ $label }}</a>
            @endforeach
        </nav>

        <details class="shared-inbox__more-filters">
            <summary class="cursor-pointer font-medium hover:text-slate-700">{{ __('More filters') }}</summary>
            <form method="GET" class="mt-2 space-y-2" data-turbo-frame="{{ $inboxTurboFrame }}">
                @if ($active)<input type="hidden" name="conversation" value="{{ $active->id }}">@endif
                @if (! empty($filters['q']))<input type="hidden" name="q" value="{{ $filters['q'] }}">@endif
                <input type="hidden" name="view" value="{{ $currentView }}">
                <div class="flex flex-wrap gap-1">
                    @foreach ($extraViews as $key => $label)
                        <a href="{{ $chipQuery($key) }}" data-turbo-frame="{{ $inboxTurboFrame }}" class="shared-inbox__chip {{ $currentView === $key ? 'shared-inbox__chip--active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
                <select name="status" class="erp-input w-full text-xs" onchange="this.form.submit()">
                    <option value="">{{ __('Status') }}</option>
                    @foreach (\App\Enums\InboxConversationStatus::cases() as $st)
                        <option value="{{ $st->value }}" @selected(($filters['status'] ?? '') === $st->value)>{{ $st->label() }}</option>
                    @endforeach
                </select>
                <input type="text" name="tag" value="{{ $filters['tag'] ?? '' }}" class="erp-input w-full text-xs" placeholder="{{ __('Tag #urgent') }}">
                <button type="submit" class="erp-btn erp-btn--secondary erp-btn--xs w-full">{{ __('Apply') }}</button>
            </form>
        </details>
    </div>

    <div class="shared-inbox__list-scroll">
        @forelse ($conversations as $conv)
            @php
                $name = $conv->display_name ?? $conv->conversation_code;
                $initial = mb_strtoupper(mb_substr($name, 0, 1));
                $isActive = ($active?->id ?? null) === $conv->id;
                $timeLabel = $conv->last_activity_at?->isToday()
                    ? $conv->last_activity_at->format('H:i')
                    : ($conv->last_activity_at?->format('d/m') ?? '');
            @endphp
            <a
                href="{{ $inboxEmbedUrl(route('admin.communications.inbox.index', array_merge(request()->query(), ['conversation' => $conv->id]))) }}"
                data-turbo-frame="{{ $inboxTurboFrame }}"
                data-conversation-id="{{ $conv->id }}"
                class="shared-inbox__conv-row {{ $isActive ? 'shared-inbox__conv-row--active' : '' }}"
            >
                <div class="shared-inbox__avatar">{{ $initial }}</div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-baseline justify-between gap-2">
                        <p class="truncate text-[15px] font-semibold text-slate-900">{{ $name }}</p>
                        @if ($timeLabel)
                            <span class="shrink-0 text-[11px] font-medium text-slate-400">{{ $timeLabel }}</span>
                        @endif
                    </div>
                    <div class="mt-0.5 flex items-center justify-between gap-2">
                        <p class="truncate text-[13px] text-slate-500">{{ $conv->last_message_preview ?? __('No messages') }}</p>
                        @if ($conv->unread_count > 0)
                            <span
                                class="shared-inbox__unread-badge"
                                data-conversation-unread-badge
                                aria-label="{{ __(':count unread', ['count' => $conv->unread_count]) }}"
                            >{{ $conv->unread_count }}</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <p class="rounded-xl bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">{{ __('No conversations match your filters.') }}</p>
        @endforelse
    </div>
    @if ($conversations->hasPages())
        <div class="shared-inbox-scrollbar shrink-0 border-t border-slate-200 p-2 text-xs">{{ $conversations->links() }}</div>
    @endif
</aside>
