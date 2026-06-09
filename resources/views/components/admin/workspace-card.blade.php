@props([
    'title',
    'description' => null,
    'icon' => 'home',
    'href' => null,
    'badge' => null,
    'comingSoon' => false,
])

<a
    @if ($href && ! $comingSoon)
        href="{{ $href }}"
        data-turbo-frame="erp-main"
    @endif
    {{ $attributes->merge([
        'class' => 'module-workspace-card'.($comingSoon || ! $href ? ' module-workspace-card--disabled' : ''),
    ]) }}
    @if ($comingSoon || ! $href) aria-disabled="true" tabindex="-1" @endif
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
