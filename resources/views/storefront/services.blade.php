<x-layouts.public :seo="$seo">
    <x-public.page-hero
        title="Printing Services in Kenya"
        intro="Digital printing, offset printing, large format, branding, design support, corporate printing, event printing and packaging for clients across Kenya."
        badge="Our Services"
        :breadcrumbs="$breadcrumbs"
    />

    <section class="public-section">
        <div class="public-container">
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($capabilities as $capability)
                    <article class="public-card public-card--soft overflow-hidden" data-animate="fade-up">
                        <x-public.media-image
                            :src="$capability['image']"
                            :alt="$capability['alt']"
                            fallback="stationery"
                            class="aspect-[16/10] w-full object-cover"
                            width="640"
                            height="400"
                        />
                        <div class="p-6">
                            <h2 class="font-display text-xl font-bold">{{ $capability['title'] }}</h2>
                            <p class="mt-3 text-sm leading-relaxed text-brand-text-secondary">{{ $capability['description'] }}</p>
                            <a href="{{ route('storefront.services.show', $capability['slug']) }}" class="public-link mt-4 inline-flex">
                                View service details
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.public>
