@props([
    'href',
])

@php
    use App\Support\Navigation\WorkspaceEmbed;

    $resolvedHref = WorkspaceEmbed::url($href);
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

<a
    href="{{ $resolvedHref }}"
    data-turbo-frame="{{ $turboFrame }}"
    data-turbo-action="advance"
    {{ $attributes }}
>
    {{ $slot }}
</a>
