@php
    $features = config('why-us.features');
    $stats = config('why-us.stats');
    $comparison = config('why-us.comparison');
    $confidence = config('why-us.confidence');
@endphp

<section id="why-us" class="public-why" data-reveal-section aria-label="Why choose Jana Prints">
    {{-- Header --}}
    <div class="public-why__header public-section public-section--dark relative overflow-hidden">
        <div class="absolute inset-0 opacity-30" data-parallax="0.2">
            <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-brand-magenta blur-[100px]"></div>
            <div class="absolute bottom-0 left-0 h-80 w-80 rounded-full bg-brand-orange blur-[100px]"></div>
        </div>

        <div class="public-container relative">
            <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
                <x-public.badge variant="light" class="mb-5">Why Jana Prints</x-public.badge>
                <h2 class="public-heading public-heading--light text-display-sm sm:text-display-md">
                    Why Businesses Choose Jana Prints
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-white/70">
                    More than printing. We combine design, production, quality control,
                    project management and delivery to ensure every project is completed
                    professionally and on time.
                </p>
            </div>
        </div>
    </div>

    {{-- Feature showcase --}}
    <div class="public-why__features">
        @foreach ($features as $index => $feature)
            <x-public.why-feature-block
                :feature="$feature"
                :reversed="$index % 2 === 1"
            />
        @endforeach
    </div>

    {{-- Statistics --}}
    <div class="public-why__stats-wrap public-section--compact bg-brand-off-white">
        <div class="public-container">
            <div class="public-why-stats" data-animate="fade-up">
                @foreach ($stats as $stat)
                    <div class="public-why-stats__item">
                        <p class="public-why-stats__value">
                            <span
                                data-counter="{{ $stat['value'] }}"
                                data-counter-suffix="{{ $stat['suffix'] }}"
                                data-counter-duration="1750"
                            >0</span>
                        </p>
                        <p class="public-why-stats__label">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Comparison panel --}}
    <div class="public-why__compare public-section--compact bg-brand-navy">
        <div class="public-container">
            <h3 class="public-why-compare__title text-center" data-animate="fade-up">
                {{ $comparison['title'] }}
            </h3>

            <div class="public-why-compare mt-10" data-animate="fade-up">
                <div class="public-why-compare__col public-why-compare__col--traditional">
                    <h4 class="public-why-compare__heading">{{ $comparison['traditional']['label'] }}</h4>
                    <ul class="public-why-compare__list">
                        @foreach ($comparison['traditional']['items'] as $item)
                            <li>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="public-why-compare__vs" aria-hidden="true">vs</div>

                <div class="public-why-compare__col public-why-compare__col--jana">
                    <h4 class="public-why-compare__heading">{{ $comparison['jana']['label'] }}</h4>
                    <ul class="public-why-compare__list">
                        @foreach ($comparison['jana']['items'] as $item)
                            <li>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Confidence panel --}}
    <div class="public-why__confidence public-section--compact bg-white border-t border-brand-gray-muted">
        <div class="public-container">
            <div class="public-why-confidence" data-animate="fade-up">
                @foreach ($confidence as $item)
                    <span class="public-why-confidence__item">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $item }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
</section>
