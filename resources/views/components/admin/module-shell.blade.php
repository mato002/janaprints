@props([
    'title',
    'description' => null,
    'primaryWorkspaces' => [],
    'activePrimary' => null,
    'secondaryWorkspaces' => [],
    'activeSecondary' => null,
    'secondaryToolbarActions' => [],
    'contentUrl' => null,
    'showContent' => true,
])

<div
    x-data="moduleWorkspaceShell()"
    @module-workspace-search.window="query = $event.detail?.query ?? ''"
    {{ $attributes->merge(['class' => 'module-shell workspace-content-shell flex min-h-0 w-full min-w-0 flex-1 flex-col gap-2 overflow-hidden']) }}
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

    @if (count($secondaryWorkspaces) > 1 || count($secondaryToolbarActions) > 0)
        <div class="module-workspace-secondary-bar">
            @if (count($secondaryWorkspaces) > 1)
                <x-admin.workspace-sub-tabs
                    class="module-workspace-secondary-bar__tabs"
                    :workspaces="$secondaryWorkspaces"
                    :active="$activeSecondary"
                />
            @else
                <div class="module-workspace-secondary-bar__tabs"></div>
            @endif

            @if (count($secondaryToolbarActions) > 0)
                <div class="module-workspace-secondary-bar__actions">
                    @foreach ($secondaryToolbarActions as $action)
                        @if ($action['modal'] ?? false)
                            <x-admin.form-modal-link
                                :href="$action['href']"
                                :variant="$action['variant'] ?? 'primary'"
                                class="shrink-0"
                            >{{ $action['label'] }}</x-admin.form-modal-link>
                        @else
                            <a
                                href="{{ $action['href'] }}"
                                @class([
                                    'shrink-0',
                                    ($action['variant'] ?? 'primary') === 'secondary' ? 'erp-btn-secondary' : 'erp-btn-primary',
                                ])
                            >{{ $action['label'] }}</a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
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
