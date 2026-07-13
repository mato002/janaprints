@props([
    'url' => null,
    'frameId' => 'module-workspace-content',
])

<x-admin.workspace-content
    :url="$url"
    :frame-id="$frameId"
    {{ $attributes->except(['url', 'frameId'])->merge(['class' => 'flex min-h-0 flex-1 flex-col overflow-hidden']) }}
>
    {{ $slot }}
</x-admin.workspace-content>
