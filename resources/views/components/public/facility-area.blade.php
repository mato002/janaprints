@props(['area', 'reversed' => false])

<article @class(['public-facility-area', 'public-facility-area--reversed' => $reversed]) data-animate="fade-up">
    <div class="public-facility-area__visual">
        <div class="public-image-reveal" data-image-reveal>
            <x-public.media-image
                :src="$area['image']"
                :alt="$area['alt']"
                fallback="production_floor"
                width="1000"
                height="667"
                class="h-full w-full object-cover"
            />
        </div>
    </div>
    <div class="public-facility-area__content">
        <h4 class="public-facility-area__title">{{ $area['title'] }}</h4>
        <p class="public-facility-area__desc">{{ $area['description'] }}</p>
    </div>
</article>
