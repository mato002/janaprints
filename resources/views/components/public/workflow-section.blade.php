@php
    $pipeline = config('facility.pipeline');
    $techniques = config('facility.capabilities');
@endphp

<section id="workflow" class="public-workflow public-section public-section--muted public-dot-pattern" data-reveal-section aria-label="How Jana Prints delivers">
    <div class="public-container">
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <x-public.badge variant="navy" class="mb-5">Production Workflow</x-public.badge>
            <h2 class="public-heading text-display-sm sm:text-display-md">
                How Jana Prints Delivers
            </h2>
            <p class="public-lead mt-4">
                A structured production workflow — from artwork review and pre-press through
                printing, finishing, quality control, packaging and delivery.
            </p>
        </div>

        <p class="public-h-scroll-hint mt-10 lg:hidden">Swipe through the workflow</p>

        <div class="public-facility-pipeline public-h-scroll public-h-scroll--workflow mt-4 lg:mt-14" data-facility-pipeline>
            @foreach ($pipeline as $index => $stage)
                <x-public.facility-pipeline-stage
                    :stage="$stage"
                    :last="$index === count($pipeline) - 1"
                />
            @endforeach
        </div>

        <div class="public-workflow-techniques mt-12 lg:mt-16" data-animate="fade-up">
            <h3 class="text-center text-sm font-semibold uppercase tracking-wider text-brand-text-muted">
                In-House Production Techniques
            </h3>
            <ul class="public-workflow-techniques__list">
                @foreach ($techniques as $technique)
                    <li>{{ $technique['title'] }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</section>
