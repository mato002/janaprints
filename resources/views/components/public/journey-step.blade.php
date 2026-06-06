@props(['step'])

@php
    $icons = [
        'document' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'design' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
        'approval' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        'print' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>',
        'package' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        'delivery' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>',
    ];
    $isHighlight = $step['highlight'] ?? false;
@endphp

<article
    @class([
        'public-journey-step',
        'public-journey-step--highlight' => $isHighlight,
    ])
    data-journey-step="{{ $step['number'] }}"
    data-animate="fade-up"
    data-animate-delay="{{ min($step['number'], 5) }}"
>
    <div class="public-journey-step__body">
        <div class="public-journey-step__header">
            <span class="public-journey-step__number" aria-hidden="true">{{ $step['number'] }}</span>
            <div class="public-journey-step__icon" aria-hidden="true">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    {!! $icons[$step['icon']] ?? $icons['document'] !!}
                </svg>
            </div>
        </div>

        @if (! empty($step['badge']))
            <span @class([
                'public-journey-step__badge',
                'public-journey-step__badge--highlight' => $isHighlight,
            ])>
                {{ $step['badge'] }}
            </span>
        @endif

        <h3 class="public-journey-step__title">{{ $step['title'] }}</h3>
        <p class="public-journey-step__desc">{{ $step['description'] }}</p>

        @if (! empty($step['trust']))
            <p class="public-journey-step__trust-label">{{ $step['trust'] }}</p>
        @endif

        <div class="public-journey-step__visual">
            <x-public.media-image
                :src="$step['image']"
                :alt="$step['alt']"
                fallback="print_press"
                class="h-full w-full object-cover"
                width="400"
                height="240"
            />
        </div>
    </div>
</article>
