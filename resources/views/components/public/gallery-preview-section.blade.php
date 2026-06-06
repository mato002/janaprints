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

        <p class="public-h-scroll-hint mt-8 lg:hidden">Swipe to view more</p>

        <div class="public-gallery-preview__grid public-h-scroll public-h-scroll--gallery-preview mt-4 lg:mt-10" data-portfolio-grid data-animate="fade-up">
            @foreach ($projects as $project)
                <x-public.portfolio-card :project="$project" />
            @endforeach
        </div>
    </div>

    <div class="public-portfolio-modal" data-portfolio-modal hidden aria-hidden="true">
        <div class="public-portfolio-modal__backdrop" data-portfolio-close></div>
        <div
            class="public-portfolio-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="portfolio-modal-title"
        >
            <button type="button" class="public-portfolio-modal__close" data-portfolio-close aria-label="Close project details">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="public-portfolio-modal__layout">
                <div class="public-portfolio-modal__media">
                    <img src="" alt="" data-portfolio-modal-image loading="lazy">
                </div>
                <div class="public-portfolio-modal__content">
                    <span class="public-portfolio-modal__category" data-portfolio-modal-category></span>
                    <h3 id="portfolio-modal-title" class="public-portfolio-modal__title" data-portfolio-modal-title></h3>
                    <p class="public-portfolio-modal__description" data-portfolio-modal-description></p>

                    <x-public.button href="{{ $quoteFormHref }}" variant="primary" class="mt-6" data-portfolio-close-on-click>
                        Request Similar Project
                    </x-public.button>
                </div>
            </div>
        </div>
    </div>
</section>
