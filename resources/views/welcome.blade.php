<x-layouts.public :seo="$seo">

    {{-- Hero --}}
    <section class="public-hero" data-testid="homepage-hero" data-reveal-section aria-label="Hero">
        <div class="public-hero__bg"></div>

        <div class="public-hero__glow-layer opacity-50" data-parallax="0.4">
            <div class="absolute -left-24 top-16 h-96 w-96 rounded-full bg-brand-magenta blur-[120px]"></div>
            <div class="absolute -right-16 bottom-0 h-[28rem] w-[28rem] rounded-full bg-brand-orange blur-[140px]"></div>
            <div class="absolute left-1/3 top-1/2 h-80 w-80 rounded-full bg-brand-purple opacity-40 blur-[100px]"></div>
            <div class="absolute bottom-1/4 right-1/3 h-64 w-64 rounded-full bg-brand-cyan opacity-20 blur-[80px]"></div>
        </div>

        <div class="public-hero__cmyk-grid absolute inset-0"></div>
        <div class="public-hero-pattern absolute inset-0 opacity-60"></div>

        <div class="public-container public-container--hero public-section--hero public-hero__content relative">
            <div class="public-hero__grid">
                {{-- Left: messaging --}}
                <div class="public-hero__copy order-1 lg:order-none" data-animate="fade-up">
                    <span class="public-hero-badge">
                        <span class="h-2 w-2 rounded-full bg-brand-cyan animate-pulse"></span>
                        Kenya's Trusted Print &amp; Branding Partner
                    </span>

                    <h1 class="font-display text-4xl font-extrabold leading-[1.06] tracking-tight text-white sm:text-5xl lg:text-[3.5rem] xl:text-display-lg">
                        Jana Prints — Professional Printing Services in Kenya
                    </h1>

                    <p class="mt-5 text-base leading-relaxed text-white/80 sm:text-lg">
                        From business cards and brochures to packaging, large-format printing,
                        corporate branding and nationwide delivery — we help businesses create
                        professional print experiences that leave lasting impressions.
                    </p>

                    <ul class="public-hero-trust public-hero-trust--mobile-compact">
                        @foreach (['Fast Turnaround', 'Artwork Approval', 'Nationwide Delivery'] as $item)
                            <li class="public-hero-trust__item">
                                <span class="public-hero-trust__icon">
                                    <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </span>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8 flex flex-col gap-4 sm:flex-row sm:items-center">
                        <x-public.button href="{{ $quoteFormHref }}" variant="gradient" size="lg">
                            Request Quote
                        </x-public.button>
                        <x-public.button href="#recent-work" variant="outline-light" size="lg">
                            View Our Work
                        </x-public.button>
                    </div>

                    <x-public.hero-stats />
                </div>

                {{-- Right: product showcase --}}
                <div class="public-hero__visual order-2 lg:order-none" data-animate="fade-right" data-animate-delay="1">
                    <x-public.hero-showcase />
                </div>
            </div>
        </div>

    </section>

    {{-- Capabilities --}}
    <x-public.services-section />

    {{-- Homepage gallery preview --}}
    <x-public.gallery-preview-section />

    {{-- Production workflow --}}
    <x-public.workflow-section />

    {{-- Inside Jana Prints (behind-the-scenes) --}}
    <x-public.inside-jana-section />

    {{-- Team behind every project --}}
    <x-public.team-showcase />

    {{-- Quality promise --}}
    <x-public.quality-promise />

    {{-- Testimonials --}}
    <x-public.testimonials-section />

    {{-- Final CTA, quote form, contact, then location map before footer --}}
    <x-public.final-cta-section />
    <x-public.quote-form />
    <x-public.contact-section />
    <x-public.contact-map-section />

</x-layouts.public>
