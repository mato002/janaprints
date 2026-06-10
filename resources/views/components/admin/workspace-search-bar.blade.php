@props([
    'moduleTitle',
    'moduleKey' => null,
])

<x-admin.module-workspace-search
    :module-title="$moduleTitle"
    :module-key="$moduleKey"
    {{ $attributes }}
/>
