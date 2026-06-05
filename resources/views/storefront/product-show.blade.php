<x-layouts.public :seo="$seo">
    <x-public.page-hero
        :title="$product['name']"
        :intro="$product['summary']"
        badge="Product"
        :breadcrumbs="$breadcrumbs"
    />

    <section class="public-section">
        <div class="public-container">
            <div class="grid gap-10 lg:grid-cols-2">
                <x-public.media-image
                    :src="$product['image']"
                    :alt="$product['alt']"
                    fallback="stationery"
                    class="aspect-[4/3] w-full rounded-brand-xl object-cover"
                    width="800"
                    height="600"
                />

                <div class="space-y-6" data-animate="fade-up">
                    <p class="text-base leading-relaxed text-brand-text-secondary">{{ $product['summary'] }}</p>
                    <p class="text-sm leading-relaxed text-brand-text-secondary">
                        Need pricing, artwork support or bulk quantities? Share your requirements and our Nairobi team will respond with a tailored quote.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <x-public.button href="{{ $quoteFormHref }}" variant="gradient" size="lg">Request a Quote</x-public.button>
                        <x-public.button href="{{ route('storefront.products') }}" variant="outline" size="lg">Back to Catalogue</x-public.button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
