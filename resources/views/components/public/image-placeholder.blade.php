@props([
    'type' => 'general',
    'label' => null,
    'aspect' => '4/3',
    'showLabel' => true,
])

@php
    $images = config('public-images');

    $map = [
        'business-cards' => ['key' => 'cards', 'alt' => 'Premium business cards and corporate stationery'],
        'stationery' => ['key' => 'stationery', 'alt' => 'Corporate letterheads and branded stationery'],
        'packaging' => ['key' => 'packaging', 'alt' => 'Custom product packaging and branded boxes'],
        'brochures' => ['key' => 'brochure', 'alt' => 'Professional brochures and marketing collateral'],
        'flyers' => ['key' => 'prepress', 'alt' => 'Flyers and promotional print materials'],
        'banners' => ['key' => 'banner', 'alt' => 'Roll-up banners and exhibition displays'],
        'large-format' => ['key' => 'signage', 'alt' => 'Large format printing and signage'],
        'vehicle-branding' => ['key' => 'vehicle', 'alt' => 'Vehicle branding and fleet graphics'],
        'promotional' => ['key' => 'merchandise', 'alt' => 'Promotional materials and branded merchandise'],
        'design' => ['key' => 'artwork', 'alt' => 'Graphic design and pre-press services'],
        'general' => ['key' => 'default', 'alt' => 'Professional commercial printing'],
    ];

    $entry = $map[$type] ?? $map['general'];
    $displayLabel = $label ?? ucwords(str_replace('-', ' ', $type));
@endphp

<div
    {{ $attributes->merge(['class' => 'public-image']) }}
    style="aspect-ratio: {{ $aspect }};"
    data-image-type="{{ $type }}"
>
    <x-public.media-image
        :src="$entry['key']"
        :alt="$entry['alt']"
        class="h-full w-full object-cover"
    />
    <div class="public-image__overlay"></div>
    @if ($showLabel)
        <span class="public-image__label">{{ $displayLabel }}</span>
    @endif
</div>
