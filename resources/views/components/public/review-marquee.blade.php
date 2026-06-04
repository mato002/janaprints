@php $reviews = config('testimonials.reviews'); @endphp

<div class="public-review-marquee" data-review-marquee>
    <div class="public-review-marquee__track">
        @foreach (array_merge($reviews, $reviews) as $review)
            <span class="public-review-marquee__item">
                <span class="public-review-marquee__stars" aria-hidden="true">★★★★★</span>
                {{ $review }}
            </span>
        @endforeach
    </div>
</div>
