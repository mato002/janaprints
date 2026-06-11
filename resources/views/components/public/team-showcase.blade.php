@php
    $team = config('facility.team');
    $teamSlots = [
        'team.management',
        'team.design',
        'team.production',
        'team.quality',
        'team.support',
        'team.dispatch',
    ];
@endphp

<section id="team" class="public-team-showcase public-section bg-white relative overflow-hidden" data-testid="homepage-team" data-reveal-section aria-label="The people behind every project">
    <div class="public-team-showcase__glow" aria-hidden="true"></div>
    <div class="public-team-showcase__accent-line" aria-hidden="true" data-team-accent-line></div>

    <div class="public-container relative">
        <div class="mx-auto max-w-3xl text-center" data-animate="fade-up">
            <x-public.badge variant="navy" class="mb-5">Our Teams</x-public.badge>
            <h2 class="public-heading text-display-sm sm:text-display-md">
                The People Behind Every Project
            </h2>
            <p class="public-lead mt-4">
                Every job is supported by people responsible for planning, design review,
                production quality, customer communication and dispatch.
            </p>
        </div>

        <p class="public-h-scroll-hint mt-10 lg:hidden">Swipe to view more</p>

        <div class="public-team-showcase__grid public-h-scroll public-h-scroll--team mt-4 lg:mt-14" data-reveal-stagger>
            @foreach ($team as $index => $member)
                <article
                    class="public-team-card"
                    data-animate="fade-up"
                    data-animate-delay="{{ min($index + 1, 5) }}"
                >
                    <div class="public-team-card__photo">
                        <x-public.media-image
                            :slot-key="$teamSlots[$index] ?? null"
                            :src="$member['photo']"
                            :alt="$member['alt']"
                            fallback-key="team"
                            class="h-full w-full object-cover"
                            width="600"
                            height="450"
                        />
                    </div>
                    <div class="public-team-card__body">
                        <h3 class="public-team-card__name">{{ $member['name'] }}</h3>
                        <p class="public-team-card__role">{{ $member['role'] }}</p>
                        <p class="public-team-card__bio">{{ $member['bio'] }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
