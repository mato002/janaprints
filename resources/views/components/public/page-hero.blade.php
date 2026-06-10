@props([
    'title',
    'intro' => null,
    'badge' => null,
    'breadcrumbs' => [],
    'wide' => false,
    'compact' => false,
])

<section @class([
    'public-page-hero public-section public-section--muted public-dot-pattern',
    'public-page-hero--compact' => $compact,
    'public-section--compact' => $compact,
]) data-reveal-section>
    <div @class(['public-container', 'public-container--wide' => $wide])>
        <x-public.breadcrumbs :items="$breadcrumbs" />

        <div @class([
            'mx-auto max-w-3xl text-center',
            'pt-6' => ! $compact,
            'pt-3 sm:pt-4' => $compact,
        ]) data-animate="fade-up">
            @if ($badge)
                <x-public.badge variant="orange" class="{{ $compact ? 'mb-3 sm:mb-4' : 'mb-5' }}">{{ $badge }}</x-public.badge>
            @endif

            <h1 @class([
                'public-heading text-display-sm sm:text-display-md',
                'text-2xl sm:text-display-sm' => $compact,
            ])>{{ $title }}</h1>

            @if ($intro)
                <p @class([
                    'public-lead mt-4',
                    'public-page-hero__intro mt-2 max-w-xl text-base leading-snug sm:mt-3 sm:text-body-lg sm:leading-relaxed' => $compact,
                ])>{{ $intro }}</p>
            @endif
        </div>
    </div>
</section>

{{ $slot }}
