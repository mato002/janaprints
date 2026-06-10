@props([
    'title',
    'description' => null,
    'primaryWorkspaces' => [],
    'activePrimary' => null,
    'secondaryWorkspaces' => [],
    'activeSecondary' => null,
    'contentUrl' => null,
    'showContent' => true,
])

<div
    x-data="moduleWorkspaceShell()"
    @module-workspace-search.window="query = $event.detail?.query ?? ''"
    {{ $attributes->merge(['class' => 'module-shell workspace-content-shell w-full min-w-0 space-y-2']) }}
>
    <x-admin.compact-workspace-header :title="$title" :description="$description">
        @isset($search)
            <x-slot:search>{{ $search }}</x-slot:search>
        @endisset
        @isset($actions)
            <x-slot:actions>{{ $actions }}</x-slot:actions>
        @endisset
    </x-admin.compact-workspace-header>

    @if (count($primaryWorkspaces) > 0)
        <x-admin.workspace-tabs
            :workspaces="$primaryWorkspaces"
            :active="$activePrimary"
        />
    @endif

    @if (count($secondaryWorkspaces) > 0)
        <x-admin.workspace-sub-tabs
            :workspaces="$secondaryWorkspaces"
            :active="$activeSecondary"
        />
    @endif

    @isset($kpis)
        <x-admin.kpi-strip>{{ $kpis }}</x-admin.kpi-strip>
    @endisset

    @isset($actionBar)
        <x-admin.action-bar>{{ $actionBar }}</x-admin.action-bar>
    @endisset

    @if ($showContent)
        <x-admin.workspace-content-shell :url="$contentUrl">
            @isset($content)
                {{ $content }}
            @else
                {{ $slot }}
            @endisset
        </x-admin.workspace-content-shell>
    @endif
</div>
