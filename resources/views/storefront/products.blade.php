<x-layouts.public :seo="$seo">
    <x-public.page-hero
        title="Print Products Catalogue"
        intro="Popular print products for businesses, schools, churches, NGOs, events and corporates across Kenya."
        badge="Products"
        :breadcrumbs="$breadcrumbs"
    />

    <section class="public-section">
        <div class="public-container">
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    <article class="public-card public-card--soft overflow-hidden" data-animate="fade-up">
                        <x-public.media-image
                            :src="$product['image']"
                            :alt="$product['alt']"
                            fallback="stationery"
                            class="aspect-[16/10] w-full object-cover"
                            width="640"
                            height="400"
                        />
                        <div class="p-6">
                            <h2 class="font-display text-xl font-bold">{{ $product['name'] }}</h2>
                            <p class="mt-3 text-sm leading-relaxed text-brand-text-secondary">{{ $product['summary'] }}</p>
                            <a href="{{ route('storefront.products.show', $product['slug']) }}" class="public-link mt-4 inline-flex">
                                View product details
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.public>
