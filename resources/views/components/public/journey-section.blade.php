@php
    $visualJourney = config('journey.visual_journey');
    $steps = config('journey.steps');
    $differentiators = config('journey.differentiators');
    $trustPanel = config('journey.trust_panel');
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
                Our structured production workflow ensures every project is professionally reviewed,
                approved, produced and delivered with accuracy and quality control.
            </p>
        </div>

        {{-- Visual storytelling strip --}}
        <div class="public-journey-visuals mt-14" data-animate="fade-up">
            @foreach ($visualJourney as $index => $stage)
                @if ($index > 0)
                    <span class="public-journey-visuals__arrow" aria-hidden="true">
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

        {{-- Timeline + trust panel --}}
        <div class="public-journey-layout mt-16">
            <div class="public-journey-main">
                <div class="public-journey-progress" aria-hidden="true">
                    <div class="public-journey-progress__track">
                        <div class="public-journey-progress__fill" data-journey-progress></div>
                    </div>
                </div>

                <div class="public-journey-timeline" data-journey-timeline>
                    @foreach ($steps as $step)
                        <x-public.journey-step :step="$step" />
                    @endforeach
                </div>
            </div>

            <aside class="public-journey-panel" data-animate="fade-left" data-animate-delay="2">
                <h3 class="public-journey-panel__title">{{ $trustPanel['title'] }}</h3>
                <ul class="public-journey-panel__list">
                    @foreach ($trustPanel['points'] as $point)
                        <li>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
            </aside>
        </div>

        {{-- Differentiators strip --}}
        <div class="public-journey-diff mt-16" data-animate="fade-up">
            @foreach ($differentiators as $item)
                <span class="public-journey-diff__item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $item }}
                </span>
            @endforeach
        </div>

    </div>
</section>
