@props(['video', 'slotKey' => null, 'slot_key' => null])

<article class="public-video-testimonial" data-animate="fade-up">
    <button type="button" class="public-video-testimonial__trigger" aria-label="Play video testimonial from {{ $video['role'] }} (coming soon)">
        <x-public.media-image
            :slot-key="$slot_key ?? $slotKey"
            :src="$video['thumbnail']"
            :alt="$video['alt']"
            fallback-key="corporate"
            class="h-full w-full object-cover"
        />
        <span class="public-video-testimonial__overlay"></span>
        <span class="public-video-testimonial__play">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
        </span>
    </button>
    <div class="public-video-testimonial__body">
        <p class="public-video-testimonial__quote">&ldquo;{{ $video['quote'] }}&rdquo;</p>
        <p class="public-video-testimonial__role">{{ $video['role'] }}</p>
    </div>
</article>
