@props([
    'badge' => null,
    'title',
    'description' => null,
    'light' => false,
    'align' => 'center',
])

@php
    $alignClass = match ($align) {
        'left' => 'text-left',
        default => 'text-center mx-auto',
    };
@endphp

<div {{ $attributes->merge(['class' => "max-w-2xl mb-16 $alignClass"]) }} data-animate="fade-up">
    @if ($badge)
        <x-public.badge :variant="$light ? 'light' : 'magenta'" class="mb-5">
            {{ $badge }}
        </x-public.badge>
    @endif

    <h2 @class([
        'public-heading text-display-sm sm:text-display-md',
        'public-heading--light' => $light,
    ])>
        {{ $title }}
    </h2>

    @if ($description)
        <p @class([
            'mt-4 public-lead',
            'text-white/70' => $light,
        ])>
            {{ $description }}
        </p>
    @endif
</div>
