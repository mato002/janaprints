@php
    $stats = config('storefront_stats.trust_strip');
@endphp

<div class="public-hero-proof" data-animate="fade-up" data-animate-delay="2" aria-label="Trust statistics">
    @foreach ($stats as $stat)
        <div class="public-hero-proof__item">
            <p class="public-hero-proof__value">
                <x-public.counter :value="$stat['value']" :suffix="$stat['suffix']" />
            </p>
            <p class="public-hero-proof__label">{{ $stat['label'] }}</p>
        </div>
    @endforeach
</div>
