@php
    $featured = config('testimonials.featured');
    $videos = config('testimonials.videos');
    $stories = config('testimonials.success_stories');
    $trustCategories = config('testimonials.trust_categories');
@endphp

<section id="testimonials" class="public-testimonials public-section bg-white" data-reveal-section aria-label="Testimonials">
    <div class="public-container">

        {{-- Header --}}
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <x-public.badge variant="magenta" class="mb-5">Client Success</x-public.badge>
            <h2 class="public-heading text-display-sm sm:text-display-md">
                What Our Customers Say
            </h2>
            <p class="public-lead mt-4">
                Businesses, schools, NGOs, institutions and corporate organizations trust Jana Prints
                for professional printing, branding and delivery services across Kenya.
            </p>
        </div>

        {{-- Featured testimonials — compact responsive grid --}}
        <p class="public-h-scroll-hint mt-10 lg:hidden">Swipe to read more</p>

        <div class="public-testimonials-grid public-h-scroll public-h-scroll--testimonials mt-4 lg:mt-12" data-animate="fade-up">
            @foreach ($featured as $index => $item)
                <x-public.featured-testimonial
                    :testimonial="$item"
                    data-animate="fade-up"
                    :data-animate-delay="min($index + 1, 4)"
                />
            @endforeach
        </div>

        {{-- Video testimonial placeholders --}}
        <div class="mt-20" data-animate="fade-up">
            <h3 class="mb-8 text-center font-display text-xl font-bold text-brand-navy sm:text-2xl">
                Customer Stories
            </h3>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($videos as $video)
                    <x-public.video-testimonial :video="$video" />
                @endforeach
            </div>
        </div>

        {{-- Success stories --}}
        <div class="mt-20" data-animate="fade-up">
            <h3 class="mb-8 text-center font-display text-xl font-bold text-brand-navy sm:text-2xl">
                Success Stories
            </h3>
            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ($stories as $story)
                    <x-public.success-story :story="$story" />
                @endforeach
            </div>
        </div>

        {{-- Trust wall --}}
        <div class="mt-20" data-animate="fade-up">
            <h3 class="mb-8 text-center text-sm font-semibold uppercase tracking-wider text-brand-text-muted">
                Trusted By Organizations Across Kenya
            </h3>
            <div class="public-testimonials-trust">
                @foreach ($trustCategories as $cat)
                    <x-public.trust-category :category="$cat['label']" :icon="$cat['icon']" />
                @endforeach
            </div>
        </div>

        {{-- Review carousel --}}
        <div class="mt-16" data-animate="fade">
            <x-public.review-marquee />
        </div>

        {{-- CTA nudge (desktop only — mobile uses header + final CTA) --}}
        <div class="mt-16 hidden text-center lg:block" data-animate="fade-up">
            <x-public.button href="{{ $quoteFormHref }}" variant="gradient" size="lg">
                Request Your Quote
            </x-public.button>
        </div>

    </div>
</section>
