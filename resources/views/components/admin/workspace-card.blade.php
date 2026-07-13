@props([
    'title',
    'description' => null,
    'icon' => 'home',
    'href' => null,
    'badge' => null,
    'comingSoon' => false,
])

@php
    use App\Support\Navigation\WorkspaceEmbed;

    // Module desk cards always navigate the outer shell (section changes / desk hops).
    $resolvedHref = $href ? WorkspaceEmbed::mainUrl($href) : null;
@endphp

<a
    @if ($resolvedHref && ! $comingSoon)
        href="{{ $resolvedHref }}"
        data-turbo-frame="erp-main"
        data-turbo-action="advance"
    @endif
    {{ $attributes->merge([
        'class' => 'module-workspace-card'.($comingSoon || ! $resolvedHref ? ' module-workspace-card--disabled' : ''),
    ]) }}
    @if ($comingSoon || ! $resolvedHref) aria-disabled="true" tabindex="-1" @endif
>
    <span class="module-workspace-card__icon">
        <x-admin.icon :name="$icon" class="h-5 w-5" />
    </span>
    <span class="module-workspace-card__body">
        <span class="module-workspace-card__title">{{ $title }}</span>
        @if ($description)
            <span class="module-workspace-card__description">{{ $description }}</span>
        @endif
    </span>
    @if ($badge)
        <span class="module-workspace-card__badge">{{ $badge }}</span>
    @endif
</a>
