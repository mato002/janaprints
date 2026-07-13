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

<x-admin.module-shell
    :title="$title"
    :description="$description"
    :primary-workspaces="$primaryWorkspaces"
    :active-primary="$activePrimary"
    :secondary-workspaces="$secondaryWorkspaces"
    :active-secondary="$activeSecondary"
    :secondary-toolbar-actions="$secondaryToolbarActions"
    :content-url="$contentUrl"
    :show-content="$showContent"
    {{ $attributes->except([
        'title',
        'description',
        'primaryWorkspaces',
        'activePrimary',
        'secondaryWorkspaces',
        'activeSecondary',
        'secondaryToolbarActions',
        'contentUrl',
        'showContent',
    ]) }}
>
    @isset($search)
        <x-slot:search>{{ $search }}</x-slot:search>
    @endisset

    {{ $slot }}
</x-admin.module-shell>
