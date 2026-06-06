@php
    $projects = config('portfolio.featured');
@endphp

<section id="recent-work" class="public-recent-work public-section bg-white" data-reveal-section aria-label="Recent work delivered">
    <div class="public-container public-container--wide">
        <div class="public-recent-work__header" data-animate="fade-up">
            <x-public.badge variant="cyan" class="mb-4">Proof Of Work</x-public.badge>
            <h2 class="public-heading text-display-sm sm:text-display-md">
                Recent Work Delivered
            </h2>
            <p class="public-lead mt-3 max-w-3xl">
                Explore a selection of projects completed for businesses, schools, NGOs,
                corporates, events, hospitality brands, and institutions across Kenya.
            </p>
            <div class="mt-5 lg:hidden">
                <x-public.button href="{{ route('storefront.gallery') }}" variant="secondary" size="sm">
                    View Full Gallery
                </x-public.button>
            </div>
        </div>

        <p class="public-h-scroll-hint mt-8 lg:hidden">Swipe to view projects</p>

        <div class="public-recent-work__grid public-h-scroll public-h-scroll--recent-work mt-4 lg:mt-10" data-animate="fade-up">
            @foreach ($projects as $project)
                <x-public.recent-work-card :project="$project" />
            @endforeach
        </div>

        <div class="mt-8 hidden text-center lg:block" data-animate="fade-up">
            <x-public.button href="{{ route('storefront.gallery') }}" variant="secondary">
                View Full Gallery
            </x-public.button>
        </div>
    </div>
</section>
