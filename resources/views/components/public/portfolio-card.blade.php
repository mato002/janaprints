@props([
    'project',
])

<article
    class="public-portfolio-card"
    data-portfolio-item
    data-category="{{ $project['category'] }}"
    itemscope
    itemtype="https://schema.org/CreativeWork"
>
    <button
        type="button"
        class="public-portfolio-card__trigger"
        data-portfolio-open
        data-project='@json($project)'
        aria-label="View work: {{ $project['title'] }}"
    >
        <div class="public-portfolio-card__media">
            <div class="public-image-reveal" data-image-reveal>
                <x-public.media-image
                    :src="$project['image']"
                    :alt="$project['alt']"
                    fallback="cards"
                    class="h-full w-full object-cover"
                    width="800"
                    height="600"
                    itemprop="image"
                />
            </div>
            <div class="public-portfolio-card__overlay">
                <div class="public-portfolio-card__overlay-content">
                    <span class="public-portfolio-card__category public-portfolio-card__category--overlay" itemprop="genre">
                        {{ $project['category_label'] }}
                    </span>
                    <h3 class="public-portfolio-card__title public-portfolio-card__title--overlay" itemprop="name">
                        {{ $project['title'] }}
                    </h3>
                    <span class="public-portfolio-card__view-btn">View Work</span>
                </div>
            </div>
        </div>

        <div class="public-portfolio-card__info md:hidden">
            <span class="public-portfolio-card__category" itemprop="genre">{{ $project['category_label'] }}</span>
            <h3 class="public-portfolio-card__title" itemprop="name">{{ $project['title'] }}</h3>
            @if (! empty($project['caption']))
                <p class="public-portfolio-card__caption">{{ $project['caption'] }}</p>
            @endif
        </div>
    </button>
</article>
