{{-- Portfolio & Recent Work Showcase --}}
@php
    $galleryService = app(\App\Services\Storefront\PublicGalleryService::class);
    $projects = ($fullPage ?? false)
        ? $galleryService->allItems()
        : $galleryService->homepageItems(12);
    $filters = $galleryService->categoriesWithItems();
    $showGalleryCta = $showGalleryCta ?? true;
    $isCompact = $compact ?? false;
@endphp

<section
    id="portfolio"
    @class([
        'public-portfolio public-section bg-white',
        'public-portfolio--compact public-section--compact' => $isCompact,
    ])
    data-reveal-section
    aria-label="Portfolio"
>
    <div @class(['public-container', 'public-container--wide' => true])>

        {{-- Section header with integrated CTA --}}
        <div class="public-portfolio-header" data-animate="fade-up">
            <div class="public-portfolio-header__content">
                @unless ($isCompact)
                    <x-public.badge variant="magenta" class="mb-4">Portfolio</x-public.badge>
                @endunless
                <h2 @class([
                    'public-heading text-display-sm sm:text-display-md',
                    'text-xl sm:text-2xl lg:text-display-sm' => $isCompact,
                ])>
                    {{ $heading ?? 'Featured Work' }}
                </h2>
                <p @class([
                    'public-lead mt-3 max-w-2xl',
                    'mt-2 max-w-lg text-sm leading-snug sm:text-base sm:leading-relaxed' => $isCompact,
                ])>
                    {{ $intro ?? 'A quick look at recent print, branding and packaging work.' }}
                </p>

                @if ($showGalleryCta && ! ($fullPage ?? false))
                    <div class="public-portfolio-header__cta-mobile mt-5 lg:hidden">
                        <x-public.button href="{{ route('storefront.gallery') }}" variant="secondary" size="sm">
                            View Full Gallery
                        </x-public.button>
                    </div>
                @endif
            </div>

            @if ($showGalleryCta && ! ($fullPage ?? false))
                <div class="public-portfolio-header__cta-desktop hidden shrink-0 lg:block" data-animate="fade-up" data-animate-delay="1">
                    <x-public.button href="{{ route('storefront.gallery') }}" variant="secondary">
                        View Full Gallery
                    </x-public.button>
                </div>
            @endif
        </div>

        {{-- Category filters — only categories with available items --}}
        @if (count($filters) > 1)
            <div
                @class([
                    'public-portfolio-filters',
                    'mt-8 lg:mt-10' => ! $isCompact,
                    'mt-4 lg:mt-6' => $isCompact,
                ])
                data-portfolio-filters
                role="tablist"
                aria-label="Filter portfolio"
            >
                @foreach ($filters as $filter)
                    <button
                        type="button"
                        role="tab"
                        class="public-portfolio-filters__btn {{ $filter['slug'] === 'all' ? 'is-active' : '' }}"
                        data-filter="{{ $filter['slug'] }}"
                        aria-selected="{{ $filter['slug'] === 'all' ? 'true' : 'false' }}"
                    >
                        {{ $filter['label'] }}
                    </button>
                @endforeach
            </div>
        @endif

        <div @class([
            'public-masonry-gallery',
            'mt-6 lg:mt-8' => ! $isCompact,
            'mt-4 lg:mt-6' => $isCompact,
        ]) data-portfolio-grid>
            @foreach ($projects as $project)
                <x-public.portfolio-card :project="$project" />
            @endforeach
        </div>

    </div>

    <x-public.portfolio-modal />
</section>
