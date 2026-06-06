{{-- Single mobile trust stats block — shown once after hero --}}
<section class="public-mobile-stats lg:hidden" aria-label="Trust statistics">
    <div class="public-container">
        <div class="public-mobile-stats__grid">
            @foreach (config('storefront_stats.mobile') as $stat)
                <article class="public-mobile-stats__item">
                    <p class="public-mobile-stats__value">
                        <x-public.counter :value="$stat['value']" :suffix="$stat['suffix']" />
                    </p>
                    <p class="public-mobile-stats__label">{{ $stat['label'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
