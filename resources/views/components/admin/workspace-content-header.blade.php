@props([
    'title',
    'description' => null,
])

<x-admin.compact-workspace-header
    :title="$title"
    :description="$description"
    {{ $attributes->merge(['class' => 'workspace-content-header mb-3']) }}
>
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset
</x-admin.compact-workspace-header>
