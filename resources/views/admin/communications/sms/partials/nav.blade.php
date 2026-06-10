@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

@if (! WorkspaceEmbed::inWorkspaceContext())
<nav class="erp-card mb-4 flex flex-wrap gap-2 p-2">
    @foreach ([
        ['route' => 'admin.communications.sms.dashboard', 'label' => __('Dashboard')],
        ['route' => 'admin.communications.sms.campaigns.index', 'label' => __('Campaigns')],
        ['route' => 'admin.communications.templates.index', 'label' => __('Templates'), 'query' => ['channel' => 'sms']],
        ['route' => 'admin.communications.sms.queues.index', 'label' => __('Queues')],
        ['route' => 'admin.communications.sms.provider-logs.index', 'label' => __('Provider logs'), 'permission' => 'communications.sms.audit'],
        ['route' => 'admin.communications.sms.credits.index', 'label' => __('Credit ledger')],
    ] as $link)
        @if (empty($link['permission']) || auth()->user()->can($link['permission']))
            <a
                href="{{ route($link['route'], $link['query'] ?? []) }}"
                data-turbo-frame="erp-main"
                class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ request()->routeIs($link['route'].'*') || (isset($link['query']) && request()->routeIs('admin.communications.templates.*')) ? 'bg-erp-accent text-white' : 'text-slate-600 hover:bg-slate-50' }}"
            >
                {{ $link['label'] }}
            </a>
        @endif
    @endforeach
</nav>
@endif
