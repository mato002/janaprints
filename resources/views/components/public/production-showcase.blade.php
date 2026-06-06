@php
    $showcase = config('facility.production_showcase');
@endphp

<div class="public-production-showcase public-section bg-brand-off-white" data-production-showcase>
    <div class="public-container">
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <h3 class="public-facility__subtitle">Built For Professional Production</h3>
            <p class="mt-4 text-base leading-relaxed text-brand-text-secondary sm:text-lg">
                From artwork approval to nationwide delivery — every stage runs through
                a structured production workflow built for commercial print.
            </p>
        </div>

        <div class="public-production-flow mt-10" data-animate="fade-up" aria-hidden="true">
            <div class="public-production-flow__track">
                <span class="public-production-flow__line" data-production-flow-line></span>
            </div>
            <div class="public-production-flow__stages">
                @foreach ($showcase['flow'] as $index => $stage)
                    <span
                        class="public-production-flow__stage"
                        data-production-flow-stage="{{ $index }}"
                    >{{ $stage }}</span>
                @endforeach
            </div>
        </div>

        <div class="public-production-split mt-10 lg:mt-14">
            <div class="public-production-split__visual max-lg:hidden" data-animate="fade-up">
                <div class="public-production-split__hero-card">
                    <div class="public-image-reveal h-full w-full" data-image-reveal>
                        <x-public.media-image
                            :src="$showcase['hero_image']"
                            :alt="$showcase['hero_alt']"
                            fallback="production_floor"
                            class="h-full w-full object-cover"
                            width="1200"
                            height="800"
                        />
                    </div>
                    <div class="public-production-split__hero-badge">
                        <span>Live Production</span>
                    </div>
                </div>
            </div>

            <div class="public-production-split__steps" data-reveal-stagger>
                @foreach ($showcase['steps'] as $index => $step)
                    <article
                        class="public-production-step"
                        data-animate="fade-up"
                        data-animate-delay="{{ min($index + 1, 5) }}"
                        data-production-step="{{ $index }}"
                    >
                        <div class="public-production-step__icon" aria-hidden="true">
                            <span>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <div class="public-production-step__body">
                            <h4 class="public-production-step__title">{{ $step['title'] }}</h4>
                            <p class="public-production-step__desc">{{ $step['description'] }}</p>
                        </div>
                        <div class="public-production-step__thumb">
                            <x-public.media-image
                                :src="$step['image']"
                                :alt="$step['alt']"
                                fallback="print_press"
                                class="h-full w-full object-cover"
                                width="120"
                                height="80"
                            />
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</div>
