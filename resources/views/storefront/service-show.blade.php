<x-layouts.public :seo="$seo">
    <x-public.page-hero
        :title="$service['title']"
        :intro="$service['description']"
        badge="Service"
        :breadcrumbs="$breadcrumbs"
    />

    <section class="public-section">
        <div class="public-container">
            <div class="grid gap-10 lg:grid-cols-2">
                <div data-animate="fade-up">
                    <x-public.media-image
                        :src="$service['image']"
                        :alt="$service['alt']"
                        fallback="stationery"
                        class="aspect-[4/3] w-full rounded-brand-xl object-cover"
                        width="800"
                        height="600"
                    />
                </div>

                <div class="space-y-8" data-animate="fade-up">
                    @if (! empty($service['benefits']))
                        <div>
                            <h2 class="font-display text-2xl font-bold">Benefits</h2>
                            <ul class="mt-4 space-y-2 text-sm leading-relaxed text-brand-text-secondary">
                                @foreach ($service['benefits'] as $benefit)
                                    <li class="flex gap-2"><span aria-hidden="true">•</span><span>{{ $benefit }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (! empty($service['use_cases']))
                        <div>
                            <h2 class="font-display text-2xl font-bold">Common Use Cases</h2>
                            <ul class="mt-4 space-y-2 text-sm leading-relaxed text-brand-text-secondary">
                                @foreach ($service['use_cases'] as $useCase)
                                    <li class="flex gap-2"><span aria-hidden="true">•</span><span>{{ $useCase }}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (! empty($service['items']))
                        <div>
                            <h2 class="font-display text-2xl font-bold">Products & Examples</h2>
                            <ul class="mt-4 flex flex-wrap gap-2">
                                @foreach ($service['items'] as $item)
                                    <li class="public-footer__badge">{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-16" data-animate="fade-up">
                <h2 class="font-display text-2xl font-bold">Frequently Asked Questions</h2>
                <div class="mt-6 space-y-4">
                    @foreach ($faqs as $faq)
                        <details class="public-card public-card--soft">
                            <summary class="cursor-pointer font-semibold">{{ $faq['question'] }}</summary>
                            <p class="mt-3 text-sm leading-relaxed text-brand-text-secondary">{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>

            <div class="mt-12 text-center">
                <x-public.button href="{{ $quoteFormHref }}" variant="gradient" size="lg">Request a Quote</x-public.button>
            </div>
        </div>
    </section>
</x-layouts.public>
