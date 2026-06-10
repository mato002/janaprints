@props([
    'workspaces' => [],
    'active' => null,
    'ariaLabel' => __('Primary workspaces'),
])

<x-admin.workspace-pill-tabs
    :workspaces="$workspaces"
    :active="$active"
    variant="primary"
    :aria-label="$ariaLabel"
    {{ $attributes->except(['workspaces', 'active', 'ariaLabel']) }}
/>
