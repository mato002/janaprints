@props(['block', 'reversed' => false])

<article
    id="inside-{{ $block['slug'] }}"
    @class([
        'public-inside-jana-block',
        'public-inside-jana-block--reversed' => $reversed,
    ])
    data-animate="fade-up"
>
    <div class="public-container">
        <div class="public-inside-jana-block__grid">
            <div class="public-inside-jana-block__visual" data-animate="{{ $reversed ? 'fade-left' : 'fade-right' }}">
                <div class="public-inside-jana-block__frame">
                    <x-public.media-image
                        :slot-key="'inside_jana.'.$block['slug']"
                        :src="$block['image']"
                        :alt="$block['alt']"
                        fallback-key="production_floor"
                        class="aspect-[4/3] w-full object-cover"
                    />
                    <div class="public-inside-jana-block__accent bg-gradient-to-br {{ $block['accent'] }}"></div>
                </div>
            </div>

            <div class="public-inside-jana-block__content">
                <x-public.badge variant="navy" class="public-inside-jana-block__badge mb-4">Inside Jana Prints</x-public.badge>
                <h3 class="public-inside-jana-block__title">{{ $block['title'] }}</h3>
                <p class="public-inside-jana-block__desc">{{ $block['description'] }}</p>

                <ul class="public-inside-jana-block__list">
                    @foreach ($block['bullets'] as $bullet)
                        <li>{{ $bullet }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</article>
