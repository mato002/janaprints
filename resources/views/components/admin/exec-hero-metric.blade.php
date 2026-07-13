@props([
    'label',
    'value',
    'hint' => null,
    'subtext' => null,
    'href' => null,
    'empty' => false,
])

@php
    use App\Support\Navigation\WorkspaceEmbed;

    $resolvedHref = $href ? (WorkspaceEmbed::url($href) ?? $href) : null;
    $turboFrame = WorkspaceEmbed::turboFrame();
@endphp

@if ($resolvedHref)
    <a href="{{ $resolvedHref }}" data-turbo-frame="{{ $turboFrame }}" data-turbo-action="advance" {{ $attributes->merge(['class' => 'exec-hero-metric exec-hero-metric--link']) }}>
@else
    <div {{ $attributes->merge(['class' => 'exec-hero-metric']) }}>
@endif
    <span class="exec-hero-metric__label">{{ $label }}</span>
    <span class="exec-hero-metric__value @if($empty) exec-hero-metric__value--empty @endif">{{ $value }}</span>
    @if ($subtext)
        <span class="exec-hero-metric__subtext">{{ $subtext }}</span>
    @endif
    @if ($hint)
        <span class="exec-hero-metric__hint">{{ $hint }}</span>
    @endif
@if ($resolvedHref)
    </a>
@else
    </div>
@endif
