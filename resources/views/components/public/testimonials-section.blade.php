@php
    $featured = config('testimonials.featured');
    $videos = config('testimonials.videos');
    $stories = config('testimonials.success_stories');
    $impactStats = config('testimonials.impact_stats');
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

        {{-- Featured testimonials --}}
        <div class="public-testimonials-rotator mt-16" data-testimonial-rotator data-animate="fade-up">
            <div class="public-testimonials-rotator__track">
                @foreach ($featured as $index => $item)
                    <x-public.featured-testimonial
                        :testimonial="$item"
                        data-testimonial-slide
                        :class="$index === 0 ? 'is-active' : ''"
                    />
                @endforeach
            </div>
            <div class="public-testimonials-rotator__nav" aria-label="Testimonial navigation">
                @foreach ($featured as $index => $item)
                    <button
                        type="button"
                        class="public-testimonials-rotator__dot {{ $index === 0 ? 'is-active' : '' }}"
                        data-testimonial-dot
                        aria-label="View testimonial {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
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

        {{-- Impact numbers --}}
        <div class="public-testimonials-impact mt-20" data-animate="fade-up">
            @foreach ($impactStats as $stat)
                <div class="public-testimonials-impact__item">
                    <p class="public-testimonials-impact__value">
                        <span
                            data-counter="{{ $stat['value'] }}"
                            data-counter-suffix="{{ $stat['suffix'] }}"
                            data-counter-duration="1750"
                        >0</span>
                    </p>
                    <p class="public-testimonials-impact__label">{{ $stat['label'] }}</p>
                </div>
            @endforeach
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

        {{-- CTA nudge --}}
        <div class="mt-16 text-center" data-animate="fade-up">
            <x-public.button href="{{ $quoteFormHref }}" variant="gradient" size="lg">
                Request Your Quote
            </x-public.button>
        </div>

    </div>
</section>
