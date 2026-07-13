@props([
    'label',
    'value',
    'href' => null,
])

@php
    use App\Support\Navigation\WorkspaceEmbed;

    $resolvedHref = $href ? (WorkspaceEmbed::url($href) ?? $href) : null;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

@if ($resolvedHref)
    <a href="{{ $resolvedHref }}" data-turbo-frame="{{ $turboFrame }}" data-turbo-action="advance" {{ $attributes->merge(['class' => 'exec-health-chip exec-health-chip--link']) }}>
@else
    <span {{ $attributes->merge(['class' => 'exec-health-chip']) }}>
@endif
    <span class="exec-health-chip__label">{{ $label }}</span>
    <span class="exec-health-chip__value">{{ $value }}</span>
@if ($resolvedHref)
    </a>
@else
    </span>
@endif
