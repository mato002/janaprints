@php
    $blocks = config('inside_jana.blocks');
@endphp

<section id="inside-jana" class="public-inside-jana" data-testid="homepage-inside-jana" data-reveal-section aria-label="Inside Jana Prints">
    <div class="public-inside-jana__header public-section bg-brand-navy relative overflow-hidden">
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
                    A behind-the-scenes look at how artwork, production, finishing and dispatch
                    come together before every job reaches the customer.
                </p>
            </div>
        </div>
    </div>

    <div class="public-inside-jana__blocks">
        @foreach ($blocks as $index => $block)
            <x-public.inside-jana-block
                :block="$block"
                :reversed="$index % 2 === 1"
            />
        @endforeach
    </div>
</section>
