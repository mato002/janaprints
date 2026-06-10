@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

@if (! WorkspaceEmbed::inWorkspaceContext())
<nav class="erp-card mb-4 flex flex-wrap gap-2 p-2">
    @foreach ([
        ['route' => 'admin.communications.logs.dashboard', 'label' => __('Dashboard')],
        ['route' => 'admin.communications.logs.timeline', 'label' => __('Timeline')],
        ['route' => 'admin.communications.logs.search', 'label' => __('Search')],
        ['route' => 'admin.communications.logs.analytics', 'label' => __('Analytics')],
        ['route' => 'admin.communications.logs.failures', 'label' => __('Failures')],
    ] as $link)
        <a
            href="{{ route($link['route']) }}"
            data-turbo-frame="erp-main"
            class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ request()->routeIs($link['route'].'*') || request()->routeIs($link['route']) ? 'bg-erp-accent text-white' : 'text-slate-600 hover:bg-slate-50' }}"
        >
            {{ $link['label'] }}
        </a>
    @endforeach
    @can('export', App\Models\Communications\CommunicationLog::class)
        <a href="{{ route('admin.communications.logs.export') }}" class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-50">{{ __('Export') }}</a>
    @endcan
</nav>
@endif
