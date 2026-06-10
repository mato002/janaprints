@php
    use App\Support\Navigation\WorkspaceEmbed;

    $embedded = WorkspaceEmbed::isEmbedded();
@endphp

<x-admin-layout
    :title="$title"
    :breadcrumbs="$embedded ? [] : ($breadcrumbs ?? [])"
    :use-workspace-navigation="! $embedded"
>
    {{ $slot }}
</x-admin-layout>
