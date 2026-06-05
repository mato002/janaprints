{{-- Portfolio & Recent Work Showcase --}}
@php
    $filters = config('portfolio.filters');
    $stats = config('portfolio.stats');
    $featured = config('portfolio.featured');
    $projects = config('portfolio.projects');
@endphp

<section id="portfolio" class="public-portfolio public-section bg-white" data-reveal-section aria-label="Portfolio">
    <div class="public-container">

        {{-- Section intro --}}
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <x-public.badge variant="magenta" class="mb-5">Portfolio</x-public.badge>
            <h2 class="public-heading text-display-sm sm:text-display-md">
                Recent Work Delivered
            </h2>
            <p class="public-lead mt-4">
                Explore a selection of projects completed for businesses, schools, NGOs, corporates,
                events, hospitality brands, and institutions across Kenya.
            </p>
        </div>

        {{-- Featured projects --}}
        <div class="mt-16" data-animate="fade-up">
            <h3 class="mb-8 text-center font-display text-xl font-bold text-brand-navy sm:text-2xl">
                Featured Projects
            </h3>
            <div class="grid gap-6 lg:grid-cols-3">
                @foreach ($featured as $index => $item)
                    <article class="public-portfolio-featured" data-animate="fade-up" data-animate-delay="{{ $index + 1 }}">
                        <div class="public-portfolio-featured__image">
                            <x-public.media-image
                                :src="$item['image']"
                                :alt="$item['alt']"
                                fallback="brochure"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div class="public-portfolio-featured__body">
                            <h4 class="public-portfolio-featured__title">{{ $item['title'] }}</h4>
                            <ul class="public-portfolio-featured__list">
                                @foreach ($item['highlights'] as $highlight)
                                    <li>{{ $highlight }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        {{-- Portfolio stats --}}
        <div class="public-portfolio-stats mt-16" data-animate="fade-up">
            @foreach ($stats as $stat)
                <div class="public-portfolio-stats__item">
                    <p class="public-portfolio-stats__value">
                        <span
                            data-counter="{{ $stat['value'] }}"
                            data-counter-suffix="{{ $stat['suffix'] }}"
                            data-counter-duration="1750"
                        >0</span>
                    </p>
                    <p class="public-portfolio-stats__label">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Filter tabs --}}
        <div class="public-portfolio-filters mt-16" data-portfolio-filters role="tablist" aria-label="Filter portfolio">
            @foreach ($filters as $filter)
                <button
                    type="button"
                    role="tab"
                    class="public-portfolio-filters__btn {{ $filter['slug'] === 'all' ? 'is-active' : '' }}"
                    data-filter="{{ $filter['slug'] }}"
                    aria-selected="{{ $filter['slug'] === 'all' ? 'true' : 'false' }}"
                >
                    {{ $filter['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Masonry grid --}}
        <div class="public-portfolio-grid mt-10" data-portfolio-grid>
            @foreach ($projects as $project)
                <x-public.portfolio-card :project="$project" />
            @endforeach
        </div>

        <p class="public-portfolio-empty mt-10 hidden" data-portfolio-empty>
            <x-public.empty-state
                icon="grid"
                title="No projects in this category"
                description="Try another filter or request a quote for a similar project."
                class="mx-auto max-w-md"
            >
                <x-public.button href="{{ $quoteFormHref }}" variant="primary" size="sm">Request A Quote</x-public.button>
            </x-public.empty-state>
        </p>

    </div>

    {{-- Project detail modal --}}
    <div class="public-portfolio-modal" data-portfolio-modal hidden aria-hidden="true">
        <div class="public-portfolio-modal__backdrop" data-portfolio-close></div>
        <div
            class="public-portfolio-modal__dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="portfolio-modal-title"
        >
            <button type="button" class="public-portfolio-modal__close" data-portfolio-close aria-label="Close project details">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="public-portfolio-modal__layout">
                <div class="public-portfolio-modal__media">
                    <img src="" alt="" data-portfolio-modal-image>
                </div>
                <div class="public-portfolio-modal__content">
                    <span class="public-portfolio-modal__category" data-portfolio-modal-category></span>
                    <h3 id="portfolio-modal-title" class="public-portfolio-modal__title" data-portfolio-modal-title></h3>
                    <p class="public-portfolio-modal__location" data-portfolio-modal-location></p>

                    <dl class="public-portfolio-modal__details">
                        <div>
                            <dt>Description</dt>
                            <dd data-portfolio-modal-description></dd>
                        </div>
                        <div>
                            <dt>Materials Used</dt>
                            <dd data-portfolio-modal-materials></dd>
                        </div>
                        <div>
                            <dt>Quantity Produced</dt>
                            <dd data-portfolio-modal-quantity></dd>
                        </div>
                        <div>
                            <dt>Completion Timeline</dt>
                            <dd data-portfolio-modal-timeline></dd>
                        </div>
                        <div>
                            <dt>Outcome</dt>
                            <dd data-portfolio-modal-outcome></dd>
                        </div>
                    </dl>

                    <x-public.button href="{{ $quoteFormHref }}" variant="primary" class="mt-6" data-portfolio-close-on-click>
                        Request Similar Project
                    </x-public.button>
                </div>
            </div>
        </div>
    </div>
</section>
