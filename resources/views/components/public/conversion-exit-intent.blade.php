{{--
    Exit-intent quote popup — structure only.
    Activation disabled via data-exit-intent-enabled="false".
    Set to "true" and wire initExitIntent() in public.js when ready to launch.
--}}
<div
    class="public-exit-intent"
    data-exit-intent-popup
    data-exit-intent-enabled="false"
    hidden
    aria-hidden="true"
    role="dialog"
    aria-labelledby="exit-intent-title"
    aria-modal="true"
>
    <div class="public-exit-intent__backdrop" data-exit-intent-close tabindex="-1"></div>
    <div class="public-exit-intent__dialog">
        <button type="button" class="public-exit-intent__close" data-exit-intent-close aria-label="Close">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <div class="public-exit-intent__content">
            <x-public.badge variant="magenta" class="mb-4">Before You Go</x-public.badge>
            <h2 id="exit-intent-title" class="public-heading text-display-sm">
                Get A Free Quote Before You Leave
            </h2>
            <p class="mt-3 text-brand-text-secondary">
                Share your project details and receive professional pricing within minutes.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <x-public.button href="{{ $quoteFormHref }}" variant="gradient" size="lg" class="public-btn--glow" data-exit-intent-close>
                    Request My Quote
                </x-public.button>
                <button type="button" class="public-btn--ghost-dark public-btn--motion-secondary public-btn--lg public-btn" data-exit-intent-close>
                    Maybe Later
                </button>
            </div>
        </div>
    </div>
</div>
