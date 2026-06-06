{{-- Compact trust stats — shown once after hero on all breakpoints --}}
<section class="public-trust-strip" aria-label="Trust statistics">
    <div class="public-container">
        <div class="public-trust-strip__grid">
            @foreach (config('storefront_stats.trust_strip') as $stat)
                <article class="public-trust-strip__item">
                    <p class="public-trust-strip__value">
                        <x-public.counter :value="$stat['value']" :suffix="$stat['suffix']" />
                    </p>
                    <p class="public-trust-strip__label">{{ $stat['label'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
