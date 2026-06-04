@props(['capability'])

<article class="public-facility-capability" data-animate="fade-up">
    <div class="public-facility-capability__image">
        <x-public.media-image
            :src="$capability['image']"
            :alt="$capability['alt']"
            fallback="print_press"
            class="h-full w-full object-cover"
        />
    </div>
    <div class="public-facility-capability__body">
        <h4 class="public-facility-capability__title">{{ $capability['title'] }}</h4>
        <p class="public-facility-capability__desc">{{ $capability['description'] }}</p>
        <p class="public-facility-capability__output">
            <span>Typical output:</span> {{ $capability['output'] }}
        </p>
    </div>
</article>
