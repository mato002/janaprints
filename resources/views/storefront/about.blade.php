<x-layouts.public :seo="$seo">
    <x-public.page-hero
        title="About Jana Prints"
        :intro="$about['headline']"
        badge="About Us"
        :breadcrumbs="$breadcrumbs"
    />

    <section class="public-section">
        <div class="public-container">
            <div class="mx-auto max-w-3xl space-y-6 text-base leading-relaxed text-brand-text-secondary" data-animate="fade-up">
                <p>{{ $about['intro'] }}</p>
                <p>{{ $about['story'] }}</p>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach ($about['values'] as $value)
                    <article class="public-card public-card--soft" data-animate="fade-up">
                        <h2 class="font-display text-xl font-bold text-brand-text-primary">{{ $value['title'] }}</h2>
                        <p class="mt-3 text-sm leading-relaxed text-brand-text-secondary">{{ $value['text'] }}</p>
                    </article>
                @endforeach
            </div>

            <div class="mt-16" data-animate="fade-up">
                <h2 class="public-heading text-2xl sm:text-3xl">Industries We Serve</h2>
                <ul class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($about['industries'] as $industry)
                        <li class="public-card public-card--soft text-sm font-medium">{{ $industry }}</li>
                    @endforeach
                </ul>
            </div>

            <div class="mt-12 flex flex-wrap justify-center gap-4">
                <x-public.button href="{{ $quoteFormHref }}" variant="gradient" size="lg">Request a Quote</x-public.button>
                <x-public.button href="{{ $contactSectionHref }}" variant="outline" size="lg">Contact Us</x-public.button>
            </div>
        </div>
    </section>
</x-layouts.public>
