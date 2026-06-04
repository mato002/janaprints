@props([
    'project',
])

@php
    $layoutClass = match ($project['layout'] ?? 'normal') {
        'hero' => 'public-portfolio-card--hero',
        'tall' => 'public-portfolio-card--tall',
        'wide' => 'public-portfolio-card--wide',
        default => '',
    };
@endphp

<article
    class="public-portfolio-card {{ $layoutClass }}"
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
        aria-label="View project: {{ $project['title'] }}"
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
            <div class="public-portfolio-card__overlay"></div>
            <div class="public-portfolio-card__hover">
                <span class="public-portfolio-card__view-btn">View Project</span>
            </div>
        </div>

        <div class="public-portfolio-card__info">
            <span class="public-portfolio-card__category" itemprop="genre">{{ $project['category_label'] }}</span>
            <h3 class="public-portfolio-card__title" itemprop="name">{{ $project['title'] }}</h3>
            <div class="public-portfolio-card__meta">
                <span itemprop="locationCreated">{{ $project['location'] }}</span>
                @if (! empty($project['quantity']))
                    <span class="public-portfolio-card__quantity">{{ $project['quantity'] }}</span>
                @endif
            </div>
        </div>
    </button>
</article>
