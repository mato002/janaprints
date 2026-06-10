@props([
    'workspaces' => [],
    'active' => null,
    'ariaLabel' => __('Secondary workspaces'),
])

<x-admin.workspace-pill-tabs
    :workspaces="$workspaces"
    :active="$active"
    variant="secondary"
    :aria-label="$ariaLabel"
    {{ $attributes->except(['workspaces', 'active', 'ariaLabel']) }}
/>
