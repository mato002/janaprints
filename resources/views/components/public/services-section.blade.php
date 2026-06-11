@php
    $capabilities = config('capabilities.capabilities');
    $trustPoints = config('capabilities.trust_points');
@endphp

<section id="services" class="public-services" data-testid="homepage-capabilities" data-reveal-section aria-label="Services and capabilities">
    {{-- Section header --}}
    <div class="public-services__header public-section public-section--muted public-dot-pattern">
        <div class="public-container">
            <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
                <x-public.badge variant="orange" class="mb-5">Our Capabilities</x-public.badge>
                <h2 class="public-heading text-display-sm sm:text-display-md">
                    Everything You Need To Print, Brand &amp; Grow
                </h2>
                <p class="public-lead mt-4">
                    From business cards and brochures to corporate branding, packaging, signage,
                    promotional merchandise and large-format printing, Jana Prints helps businesses
                    create lasting impressions.
                </p>
            </div>
        </div>
    </div>

    {{-- Capability blocks --}}
    <div class="public-services__blocks">
        @foreach ($capabilities as $index => $capability)
            <x-public.capability-block
                :capability="$capability"
                :reversed="$index % 2 === 1"
                :trust-points="$trustPoints"
            />
        @endforeach
    </div>
</section>
