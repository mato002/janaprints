{{-- Trust & Social Proof — immediately below hero --}}
<section id="trust" class="public-trust public-section bg-white" data-reveal-section aria-label="Trust and social proof">
    <div class="public-container">

        {{-- Section intro --}}
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <x-public.badge variant="navy" class="mb-5">Established &amp; Trusted</x-public.badge>
            <h2 class="public-heading text-display-sm sm:text-display-md">
                Trusted By Businesses Across Kenya
            </h2>
            <p class="public-lead mt-4">
                From startups and schools to NGOs, corporates, manufacturers, and government institutions,
                Jana Prints delivers professional print solutions at scale.
            </p>
        </div>

        {{-- Trust imagery strip --}}
        <div class="public-trust-visuals mt-14" data-animate="fade-up" data-animate-delay="1">
            @foreach ([
                ['src' => 'brochure', 'alt' => 'Brochure and catalogue printing'],
                ['src' => 'stationery', 'alt' => 'Corporate stationery printing'],
                ['src' => 'packaging', 'alt' => 'Product packaging printing'],
                ['src' => 'merchandise', 'alt' => 'Promotional merchandise printing'],
                ['src' => 'cards', 'alt' => 'Business card printing'],
            ] as $visual)
                <div class="public-trust-visuals__item">
                    <x-public.media-image
                        :src="$visual['src']"
                        :alt="$visual['alt']"
                        class="h-full w-full object-cover rounded-brand-md"
                        width="200"
                        height="200"
                    />
                </div>
            @endforeach
        </div>

        {{-- Statistics strip (desktop only — mobile uses single stats block after hero) --}}
        <div class="public-trust-stats mt-14 max-lg:hidden">
            @foreach (config('storefront_stats.trust') as $index => $stat)
                <article
                    class="public-trust-stat-card"
                    data-animate="fade-up"
                    data-animate-delay="{{ ($index % 3) + 1 }}"
                >
                    <p class="public-trust-stat-card__value">
                        <x-public.counter :value="$stat['value']" :suffix="$stat['suffix']" />
                    </p>
                    <p class="public-trust-stat-card__label">{{ $stat['label'] }}</p>
                </article>
            @endforeach
        </div>

        {{-- Testimonials (below statistics) --}}
        <div class="mt-16" data-animate="fade-up">
            <x-public.testimonial-carousel />
        </div>

        {{-- Client logo wall --}}
        <div class="mt-16" data-animate="fade-up">
            <h3 class="mb-8 text-center text-sm font-semibold uppercase tracking-wider text-brand-text-muted">
                Trusted By
            </h3>
            <x-public.client-logos />
        </div>

        {{-- Trust chips --}}
        <div class="public-trust-chips mt-16" data-animate="fade-up">
            @foreach ([
                'Quality Controlled Production',
                'Dedicated Account Managers',
                'Nationwide Delivery',
                'Secure Artwork Approval',
                'Professional Design Support',
                'Corporate Billing Available',
            ] as $chip)
                <span class="public-trust-chip">
                    <svg class="public-trust-chip__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $chip }}
                </span>
            @endforeach
        </div>

    </div>
</section>
