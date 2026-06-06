@php
    $galleryService = app(\App\Services\Storefront\PublicGalleryService::class);
    $projects = $galleryService->homepageItems(6);
@endphp

<section id="recent-work" class="public-gallery-preview public-section bg-white" data-reveal-section aria-label="Recent work gallery preview">
    <div class="public-container public-container--wide">
        <div class="public-portfolio-header" data-animate="fade-up">
            <div class="public-portfolio-header__content">
                <x-public.badge variant="magenta" class="mb-4">Featured Work</x-public.badge>
                <h2 class="public-heading text-display-sm sm:text-display-md">
                    Recent Work Delivered
                </h2>
                <p class="public-lead mt-3 max-w-2xl">
                    A quick look at recent print, branding, packaging, and large-format projects.
                </p>

                <div class="public-portfolio-header__cta-mobile mt-5 lg:hidden">
                    <x-public.button href="{{ route('storefront.gallery') }}" variant="secondary" size="sm">
                        View Full Gallery
                    </x-public.button>
                </div>
            </div>

            <div class="public-portfolio-header__cta-desktop hidden shrink-0 lg:block" data-animate="fade-up" data-animate-delay="1">
                <x-public.button href="{{ route('storefront.gallery') }}" variant="secondary">
                    View Full Gallery
                </x-public.button>
            </div>
        </div>

        <div class="public-masonry-gallery mt-8 lg:mt-10" data-portfolio-grid data-animate="fade-up">
            @foreach ($projects as $project)
                <x-public.portfolio-card :project="$project" />
            @endforeach
        </div>
    </div>

    <x-public.portfolio-modal />
</section>
