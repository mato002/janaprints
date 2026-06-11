@props(['testimonial', 'slotKey' => null, 'slot_key' => null])

<article {{ $attributes->merge(['class' => 'public-testimonial-card']) }}>
    <div class="public-testimonial-card__thumb">
        <x-public.media-image
            :slot-key="$slot_key ?? $slotKey"
            :src="$testimonial['photo']"
            :alt="$testimonial['alt']"
            fallback-key="cards"
            width="120"
            height="80"
            class="h-full w-full object-cover"
        />
    </div>

    <div class="public-testimonial-card__stars" aria-label="5 out of 5 stars">
        @for ($i = 0; $i < 5; $i++)
            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        @endfor
    </div>

    <blockquote class="public-testimonial-card__quote">
        &ldquo;{{ $testimonial['quote'] }}&rdquo;
    </blockquote>

    <footer class="public-testimonial-card__meta">
        <cite class="public-testimonial-card__name">{{ $testimonial['name'] }}</cite>
        <span class="public-testimonial-card__org">{{ $testimonial['organization'] }}</span>
        <span class="public-testimonial-card__detail">{{ $testimonial['location'] }} · {{ $testimonial['project_type'] }}</span>
    </footer>
</article>
