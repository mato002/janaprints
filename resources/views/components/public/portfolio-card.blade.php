@props([
    'project',
])

<article
    class="public-masonry-item public-portfolio-card"
    data-portfolio-item
    data-category="{{ $project['category'] }}"
    itemscope
    itemtype="https://schema.org/CreativeWork"
>
    <button
        type="button"
        class="public-masonry-item__trigger public-portfolio-card__trigger"
        data-portfolio-open
        data-project='@json($project)'
        aria-label="View project: {{ $project['title'] }}"
    >
        <div class="public-masonry-item__media public-portfolio-card__media">
            <x-public.media-image
                :src="$project['image']"
                :alt="$project['alt']"
                fallback="cards"
                class="public-masonry-item__image"
                width="800"
                itemprop="image"
            />

            <div class="public-masonry-item__overlay public-portfolio-card__overlay" aria-hidden="true">
                <div class="public-masonry-item__overlay-content public-portfolio-card__overlay-content">
                    <span class="public-masonry-item__category public-portfolio-card__category public-portfolio-card__category--overlay" itemprop="genre">
                        {{ $project['category_label'] }}
                    </span>
                    <h3 class="public-masonry-item__title public-portfolio-card__title public-portfolio-card__title--overlay" itemprop="name">
                        {{ $project['title'] }}
                    </h3>
                </div>
            </div>
        </div>
    </button>
</article>
