@php
    $contact = config('conversion.contact');
    $mapEmbed = $contact['map_embed'] ?? null;

    if (! $mapEmbed && isset($contact['map_latitude'], $contact['map_longitude'])) {
        $mapEmbed = sprintf(
            'https://maps.google.com/maps?q=%F,%F&z=%d&hl=en&ie=UTF8&iwloc=&output=embed',
            $contact['map_latitude'],
            $contact['map_longitude'],
            $contact['map_zoom'] ?? 15,
        );
    }
@endphp

@if ($mapEmbed)
    <div class="public-contact-map" data-animate="fade-up">
        <div class="public-contact-map__frame">
            <iframe
                src="{{ $mapEmbed }}"
                title="Jana Prints location map"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
            ></iframe>
        </div>

        <div class="public-contact-map__caption">
            <div class="public-contact-map__caption-copy">
                <div class="public-contact-map__title">
                    <span class="public-contact-icon-bubble" aria-hidden="true">
                        <svg class="public-contact-icon" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <h3 class="font-display text-lg font-bold text-brand-navy">Find Us</h3>
                </div>
                <p class="mt-2 text-sm text-brand-text-secondary">{{ $contact['address'] }}</p>
                @if (! empty($contact['address_detail']))
                    <p class="mt-1 text-sm text-brand-text-muted">{{ $contact['address_detail'] }}</p>
                @endif
            </div>

            <a
                href="{{ $contact['map_url'] }}"
                target="_blank"
                rel="noopener noreferrer"
                class="public-contact-map__link"
            >
                Open in Google Maps
                <svg class="public-contact-icon" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    </div>
@endif
