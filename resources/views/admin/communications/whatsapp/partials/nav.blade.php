<nav class="erp-card mb-4 flex flex-wrap gap-2 p-2">
    @foreach ([
        ['route' => 'admin.communications.whatsapp.inbox', 'label' => __('Inbox')],
        ['route' => 'admin.communications.whatsapp.conversations.index', 'label' => __('Conversations')],
        ['route' => 'admin.communications.whatsapp.templates.index', 'label' => __('Templates')],
        ['route' => 'admin.communications.templates.index', 'label' => __('COM-1 Templates'), 'query' => ['channel' => 'whatsapp']],
        ['route' => 'admin.communications.whatsapp.queue.index', 'label' => __('Queue')],
        ['route' => 'admin.communications.whatsapp.delivery.index', 'label' => __('Delivery status'), 'permission' => 'communications.whatsapp.audit'],
        ['route' => 'admin.communications.whatsapp.analytics', 'label' => __('Analytics')],
    ] as $link)
        @if (empty($link['permission']) || auth()->user()->can($link['permission']))
            <a
                href="{{ route($link['route'], $link['query'] ?? []) }}"
                data-turbo-frame="erp-main"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ request()->routeIs($link['route'].'*') || request()->routeIs($link['route']) ? 'bg-erp-accent text-white' : 'text-slate-600 hover:bg-slate-50' }}"
            >
                {{ $link['label'] }}
            </a>
        @endif
    @endforeach
</nav>
