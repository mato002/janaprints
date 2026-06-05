<x-layouts.public :seo="$seo">
    <x-public.page-hero
        title="Request a Print Quote"
        intro="Share your project details for business cards, flyers, banners, brochures, branding, bulk printing or same-day requests where available."
        badge="Get a Quote"
        :breadcrumbs="$breadcrumbs"
    />

    <x-public.quote-form />

    <section class="public-section public-section--muted">
        <div class="public-container">
            <h2 class="font-display text-2xl font-bold">Frequently Asked Questions</h2>
            <div class="mt-6 space-y-4">
                @foreach ($faq as $item)
                    <details class="public-card public-card--soft">
                        <summary class="cursor-pointer font-semibold">{{ $item['question'] }}</summary>
                        <p class="mt-3 text-sm leading-relaxed text-brand-text-secondary">{{ $item['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.public>
