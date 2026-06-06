@php
    $visualJourney = config('journey.visual_journey');
    $steps = config('journey.steps');
    $trustPanel = config('journey.trust_panel');
    $assurance = config('journey.assurance');
@endphp

<section id="workflow" class="public-journey public-section public-section--muted public-dot-pattern" data-reveal-section aria-label="Production journey">
    <div class="public-container">

        {{-- Header --}}
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <x-public.badge variant="cyan" class="mb-5">How It Works</x-public.badge>
            <h2 class="public-heading text-display-sm sm:text-display-md">
                From Idea To Delivery
            </h2>
            <p class="public-lead mt-4">
                A clear, structured workflow — from quote request and artwork review
                through approval, production, finishing and delivery.
            </p>
        </div>

        {{-- Quick visual strip --}}
        <div class="public-journey-visuals mt-10 lg:mt-14" data-animate="fade-up" aria-hidden="true">
            @foreach ($visualJourney as $index => $stage)
                @if ($index > 0)
                    <span class="public-journey-visuals__arrow">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif
                <div class="public-journey-visuals__stage">
                    <div class="public-journey-visuals__thumb">
                        <x-public.media-image
                            :src="$stage['image']"
                            :alt="$stage['alt']"
                            fallback="artwork"
                            class="h-full w-full object-cover"
                        />
                    </div>
                    <span class="public-journey-visuals__label">{{ $stage['label'] }}</span>
                </div>
            @endforeach
        </div>

        {{-- Process grid + trust panel --}}
        <div class="public-journey-layout mt-12 lg:mt-16">
            <div class="public-journey-main">
                <div class="public-journey-progress" aria-hidden="true">
                    <div class="public-journey-progress__track">
                        <div class="public-journey-progress__fill" data-journey-progress></div>
                    </div>
                </div>

                <p class="public-h-scroll-hint lg:hidden">Swipe through the process</p>

                <div class="public-journey-timeline public-h-scroll public-h-scroll--journey mt-4 lg:mt-0" data-journey-timeline>
                    @foreach ($steps as $step)
                        <x-public.journey-step :step="$step" />
                    @endforeach
                </div>

                {{-- Unified assurance banner --}}
                <div class="public-journey-assurance mt-8" data-animate="fade-up">
                    <span class="public-journey-assurance__icon" aria-hidden="true">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
                    <div>
                        <p class="public-journey-assurance__title">{{ $assurance['title'] }}</p>
                        <p class="public-journey-assurance__subtitle">{{ $assurance['subtitle'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Trust panel — collapsible on mobile, sticky on desktop --}}
            <details class="public-journey-panel" data-journey-panel>
                <summary class="public-journey-panel__summary lg:hidden">
                    <span>{{ $trustPanel['title'] }}</span>
                    <svg class="public-journey-panel__chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>

                <div class="public-journey-panel__content" data-animate="fade-left" data-animate-delay="2">
                    <h3 class="public-journey-panel__title hidden lg:block">{{ $trustPanel['title'] }}</h3>

                    <p class="public-journey-panel__headline">{{ $trustPanel['headline'] }}</p>

                    <ul class="public-journey-panel__list">
                        @foreach ($trustPanel['points'] as $point)
                            <li>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $point }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="public-journey-panel__cta">
                        <x-public.button href="{{ $quoteFormHref }}" variant="gradient" class="w-full justify-center">
                            Start Your Project
                        </x-public.button>
                    </div>
                </div>
            </details>
        </div>

    </div>
</section>
