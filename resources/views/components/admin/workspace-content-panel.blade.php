@props([
    'url' => null,
    'frameId' => 'module-workspace-content',
])

<x-admin.workspace-content-shell
    :url="$url"
    :frame-id="$frameId"
    {{ $attributes->except(['url', 'frameId']) }}
>
    {{ $slot }}
</x-admin.workspace-content-shell>
