@props([
    'showLabel' => true,
    'compact' => false,
])

@php
    $images = config('public-images');
    $resolver = app(\App\Services\Website\WebsiteMediaResolver::class);
    $defaultImage = $resolver->resolvePath('default');
    $cards = [
        ['type' => 'business-cards', 'key' => 'cards', 'label' => 'Business Cards', 'alt' => 'Premium business cards'],
        ['type' => 'brochures', 'key' => 'brochure', 'label' => 'Brochures', 'alt' => 'Professional brochures'],
        ['type' => 'packaging', 'key' => 'packaging', 'label' => 'Packaging', 'alt' => 'Custom packaging'],
        ['type' => 'banners', 'key' => 'banner', 'label' => 'Roll-Up Banners', 'alt' => 'Roll-up banners'],
        ['type' => 'stationery', 'key' => 'stationery', 'label' => 'Corporate Stationery', 'alt' => 'Corporate stationery'],
        ['type' => 'promotional', 'key' => 'merchandise', 'label' => 'Branded Merchandise', 'alt' => 'Branded merchandise'],
        ['type' => 'large-format', 'key' => 'print_press', 'label' => 'Large Format Prints', 'alt' => 'Large format prints'],
    ];
    $layoutClasses = [
        'business-cards' => 'public-hero-showcase__card--main',
        'brochures' => 'public-hero-showcase__card--brochures',
        'packaging' => 'public-hero-showcase__card--packaging',
        'banners' => 'public-hero-showcase__card--banners',
        'stationery' => 'public-hero-showcase__card--stationery',
        'promotional' => 'public-hero-showcase__card--merchandise',
        'large-format' => 'public-hero-showcase__card--large-format',
    ];
    $delays = ['0s', '0.8s', '1.2s', '0.4s', '1.6s', '2s', '1s'];
    $stripCards = array_slice($cards, 0, 5);
@endphp

{{-- Mobile: compact horizontal visual strip --}}
<div class="public-hero-strip lg:hidden" data-parallax="0.08" aria-hidden="true">
    <div class="public-hero-strip__track">
        @foreach ($stripCards as $index => $card)
            <figure class="public-hero-strip__item">
                <img
                    src="{{ $resolver->resolvePath($card['key']) }}"
                    alt=""
                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                    decoding="async"
                    width="160"
                    height="120"
                    onerror="if(!this.dataset.fallbackApplied){this.dataset.fallbackApplied='1';this.src='{{ $defaultImage }}';}"
                >
                @if ($showLabel)
                    <figcaption>{{ $card['label'] }}</figcaption>
                @endif
            </figure>
        @endforeach
    </div>
</div>

{{-- Desktop: floating collage --}}
<div {{ $attributes->merge(['class' => 'public-hero-showcase max-lg:hidden']) }} data-parallax="0.15">
    <div class="public-hero-showcase__glow"></div>

    @foreach ($cards as $index => $card)
        <figure
            class="public-hero-showcase__card {{ $layoutClasses[$card['type']] ?? '' }}"
            style="animation-delay: {{ $delays[$index] ?? '0s' }};"
            data-image-type="{{ $card['type'] }}"
        >
            <img
                src="{{ $resolver->resolvePath($card['key']) }}"
                alt="{{ $resolver->resolveAlt($card['key']) ?: $card['alt'] }}"
                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                decoding="async"
                @if ($index === 0) fetchpriority="high" @endif
                width="600"
                height="450"
                onerror="if(!this.dataset.fallbackApplied){this.dataset.fallbackApplied='1';this.src='{{ $defaultImage }}';}"
            >
            @if ($showLabel)
                <figcaption class="public-hero-showcase__label">{{ $card['label'] }}</figcaption>
            @endif
        </figure>
    @endforeach
</div>
