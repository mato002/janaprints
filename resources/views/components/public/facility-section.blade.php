@php
    $pipeline = config('facility.pipeline');
    $areas = config('facility.facility_areas');
    $team = config('facility.team');
    $qualitySteps = config('facility.quality_steps');
    $capabilities = config('facility.capabilities');
    $trustPoints = config('facility.trust_points');
    $gallery = config('facility.gallery');
@endphp

<section id="facility" class="public-facility" data-reveal-section aria-label="Production facility">
    {{-- Header --}}
    <div class="public-facility__header public-section bg-brand-navy relative overflow-hidden">
        <div class="absolute inset-0 opacity-25" data-parallax="0.15">
            <div class="absolute left-0 top-0 h-64 w-64 rounded-full bg-brand-cyan blur-[100px]"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-brand-orange blur-[120px]"></div>
        </div>
        <div class="public-container relative">
            <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
                <x-public.badge variant="light" class="mb-5">Behind The Scenes</x-public.badge>
                <h2 class="public-heading public-heading--light text-display-sm sm:text-display-md">
                    Inside Jana Prints
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-white/70">
                    Take a look behind the scenes and discover how every project moves from
                    artwork approval to professional production and delivery.
                </p>
            </div>
        </div>
    </div>

    {{-- Production pipeline --}}
    <div class="public-section bg-white">
        <div class="public-container">
            <div class="public-facility-pipeline" data-facility-pipeline>
                @foreach ($pipeline as $index => $stage)
                    <x-public.facility-pipeline-stage
                        :stage="$stage"
                        :last="$index === count($pipeline) - 1"
                    />
                @endforeach
            </div>
        </div>
    </div>

    {{-- Our facility --}}
    <div class="public-facility__areas bg-brand-off-white">
        <div class="public-container public-section--compact">
            <h3 class="public-facility__subtitle text-center" data-animate="fade-up">
                Built For Professional Production
            </h3>
        </div>
        @foreach ($areas as $index => $area)
            <x-public.facility-area :area="$area" :reversed="$index % 2 === 1" />
        @endforeach
    </div>

    {{-- Meet the team --}}
    <div class="public-section bg-white">
        <div class="public-container">
            <h3 class="public-facility__subtitle text-center" data-animate="fade-up">
                The People Behind Every Project
            </h3>
            <div class="public-facility-team-grid mt-12">
                @foreach ($team as $member)
                    <x-public.facility-team-card :member="$member" />
                @endforeach
            </div>
        </div>
    </div>

    {{-- Quality control --}}
    <div class="public-section public-section--muted public-dot-pattern">
        <div class="public-container">
            <h3 class="public-facility__subtitle text-center" data-animate="fade-up">
                Quality Is Built Into Every Stage
            </h3>
            <div class="public-facility-qc mt-12" data-facility-qc>
                @foreach ($qualitySteps as $step)
                    <article class="public-facility-qc__step" data-animate="fade-up" data-animate-delay="{{ min($step['number'], 5) }}">
                        <span class="public-facility-qc__number">{{ $step['number'] }}</span>
                        <div class="public-facility-qc__body">
                            <h4 class="public-facility-qc__title">{{ $step['title'] }}</h4>
                            <p class="public-facility-qc__desc">{{ $step['description'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Equipment & capabilities --}}
    <div class="public-section bg-white">
        <div class="public-container">
            <h3 class="public-facility__subtitle text-center" data-animate="fade-up">
                Production Capabilities
            </h3>
            <div class="public-facility-capabilities mt-12">
                @foreach ($capabilities as $cap)
                    <x-public.facility-capability-card :capability="$cap" />
                @endforeach
            </div>
        </div>
    </div>

    {{-- Why this matters --}}
    <div class="public-facility-trust public-section--compact bg-brand-navy">
        <div class="public-container text-center" data-animate="fade-up">
            <h3 class="font-display text-xl font-bold text-white sm:text-2xl">
                Professional Processes Create Better Results
            </h3>
            <div class="public-facility-trust__grid mt-8">
                @foreach ($trustPoints as $point)
                    <span class="public-facility-trust__item">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $point }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Live production gallery --}}
    <div class="public-section bg-brand-off-white">
        <div class="public-container">
            <h3 class="public-facility__subtitle text-center" data-animate="fade-up">
                Live Production Gallery
            </h3>
            <div class="public-facility-gallery mt-12" data-reveal-stagger>
                @foreach ($gallery as $item)
                    @php
                        $layoutClass = match ($item['layout'] ?? 'normal') {
                            'tall' => 'public-facility-gallery__item--tall',
                            'wide' => 'public-facility-gallery__item--wide',
                            'hero' => 'public-facility-gallery__item--hero',
                            default => '',
                        };
                    @endphp
                    <figure @class(['public-facility-gallery__item', $layoutClass]) data-animate="fade-up">
                        <div class="public-image-reveal h-full w-full" data-image-reveal>
                            <x-public.media-image
                                :src="$item['image']"
                                :alt="$item['alt']"
                                fallback="print_press"
                                width="800"
                                height="600"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <figcaption class="public-facility-gallery__caption">{{ $item['alt'] }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </div>
</section>
