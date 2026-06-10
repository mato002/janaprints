@props([
    'url' => null,
    'frameId' => 'module-workspace-content',
])

<x-admin.workspace-content
    :url="$url"
    :frame-id="$frameId"
    {{ $attributes->except(['url', 'frameId']) }}
>
    {{ $slot }}
</x-admin.workspace-content>
