@props([
    'title',
    'intro' => null,
    'badge' => null,
    'breadcrumbs' => [],
    'wide' => false,
])

<section class="public-page-hero public-section public-section--muted public-dot-pattern" data-reveal-section>
    <div @class(['public-container', 'public-container--wide' => $wide])>
        <x-public.breadcrumbs :items="$breadcrumbs" />

        <div class="mx-auto max-w-3xl pt-6 text-center" data-animate="fade-up">
            @if ($badge)
                <x-public.badge variant="orange" class="mb-5">{{ $badge }}</x-public.badge>
            @endif

            <h1 class="public-heading text-display-sm sm:text-display-md">{{ $title }}</h1>

            @if ($intro)
                <p class="public-lead mt-4">{{ $intro }}</p>
            @endif
        </div>
    </div>
</section>

{{ $slot }}
