@props([
    'moduleTitle',
    'moduleKey' => null,
])

<x-admin.workspace-search-bar
    :module-title="$moduleTitle"
    :module-key="$moduleKey"
    {{ $attributes }}
/>
