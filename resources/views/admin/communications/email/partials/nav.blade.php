@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

@if (! WorkspaceEmbed::inWorkspaceContext())
<nav class="erp-card mb-4 flex flex-wrap gap-2 p-2" aria-label="{{ __('Email administration') }}">
    @foreach ([
        ['route' => 'admin.communications.email.dashboard', 'label' => __('Mailbox')],
        ['route' => 'admin.communications.email.compose', 'label' => __('Compose'), 'permission' => 'communications.email.send'],
        ['route' => 'admin.communications.email.campaigns.index', 'label' => __('Campaigns')],
        ['route' => 'admin.communications.email.reports.index', 'label' => __('Reports'), 'permission' => 'communications.email.audit'],
        ['route' => 'admin.communications.email.analytics', 'label' => __('Operations'), 'permission' => 'communications.email.manage|communications.email.audit'],
        ['route' => 'admin.communications.email.delivery.index', 'label' => __('Delivery tracking'), 'permission' => 'communications.email.audit'],
        ['route' => 'admin.communications.email.settings', 'label' => __('Settings'), 'permission' => 'communications.email.manage'],
        ['route' => 'admin.communications.email.certification', 'label' => __('Certification'), 'permission' => 'communications.email.manage'],
    ] as $link)
        @php
            $allowed = empty($link['permission'])
                || collect(explode('|', $link['permission']))->contains(fn ($permission) => auth()->user()->can($permission));
        @endphp
        @if ($allowed)
            <a href="{{ route($link['route'], $link['query'] ?? []) }}" data-turbo-frame="erp-main" class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ request()->routeIs($link['route'].'*') || request()->routeIs($link['route']) ? 'bg-erp-accent text-white' : 'text-slate-600 hover:bg-slate-50' }}">{{ $link['label'] }}</a>
        @endif
    @endforeach
</nav>
@endif
