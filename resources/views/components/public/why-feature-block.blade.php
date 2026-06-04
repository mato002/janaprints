@props(['feature', 'reversed' => false])

<article
    id="why-{{ $feature['slug'] }}"
    @class([
        'public-why-feature',
        'public-why-feature--reversed' => $reversed,
        'public-why-feature--featured' => $feature['featured'] ?? false,
    ])
    data-animate="fade-up"
>
    <div class="public-container">
        <div class="public-why-feature__grid">
            <div class="public-why-feature__visual" data-animate="{{ $reversed ? 'fade-left' : 'fade-right' }}">
                <div class="public-why-feature__frame">
                    <x-public.media-image
                        :src="$feature['image']"
                        :alt="$feature['alt']"
                        fallback="proof"
                        class="aspect-[4/3] w-full object-cover"
                    />
                    <div class="public-why-feature__accent bg-gradient-to-br {{ $feature['accent'] }}"></div>
                </div>
                <span class="public-why-feature__float public-why-feature__float--number">{{ $feature['number'] }}</span>
            </div>

            <div class="public-why-feature__content">
                <span class="public-why-feature__badge bg-gradient-to-r {{ $feature['accent'] }}">
                    Advantage {{ $feature['number'] }}
                </span>

                <h3 class="public-why-feature__title">{{ $feature['title'] }}</h3>
                <p class="public-why-feature__desc">{{ $feature['description'] }}</p>

                <p @class([
                    'public-why-feature__trust',
                    'public-why-feature__trust--featured' => $feature['featured'] ?? false,
                ])>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    {{ $feature['trust'] }}
                </p>
            </div>
        </div>
    </div>
</article>
