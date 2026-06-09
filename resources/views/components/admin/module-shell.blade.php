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

<div {{ $attributes->merge(['class' => 'module-shell w-full min-w-0 space-y-4']) }}>
    <x-admin.page-header :title="$title" :description="$description">
        @isset($actions)
            <x-slot:actions>{{ $actions }}</x-slot:actions>
        @endisset
    </x-admin.page-header>

    @isset($search)
        <div class="module-shell__search">{{ $search }}</div>
    @endisset

    @if (count($primaryWorkspaces) > 0)
        <x-admin.workspace-switcher
            :workspaces="$primaryWorkspaces"
            :active="$activePrimary"
        />
    @endif

    @if (count($secondaryWorkspaces) > 0)
        <x-admin.secondary-workspace-switcher
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
        <x-admin.workspace-content :url="$contentUrl">
            @isset($content)
                {{ $content }}
            @else
                {{ $slot }}
            @endisset
        </x-admin.workspace-content>
    @endif
</div>
