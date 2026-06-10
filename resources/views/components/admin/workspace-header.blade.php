@props([
    'title' => '',
    'description' => null,
])

<x-admin.compact-workspace-header
    :title="$title"
    :description="$description"
    {{ $attributes->except(['title', 'description']) }}
>
    @isset($search)
        <x-slot:search>{{ $search }}</x-slot:search>
    @endisset
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset
</x-admin.compact-workspace-header>
