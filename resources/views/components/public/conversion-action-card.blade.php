@props(['card', 'whatsappUrl' => null])

@php
    $href = match (true) {
        $card['href'] === 'whatsapp' => $whatsappUrl,
        ($card['slug'] ?? '') === 'consultation' => $consultationHref ?? ($contactSectionHref ?? '#contact'),
        $card['href'] === '#quote-form' => $quoteFormHref ?? '#quote-form',
        $card['href'] === '#contact' => $contactSectionHref ?? '#contact',
        default => $card['href'],
    };
    $isExternal = str_starts_with($href, 'http') || str_starts_with($href, 'tel:');
    $accentClass = match ($card['accent'] ?? 'magenta') {
        'purple' => 'public-conversion-action--purple',
        'green' => 'public-conversion-action--green',
        'cyan' => 'public-conversion-action--cyan',
        default => 'public-conversion-action--magenta',
    };
@endphp

<a
    href="{{ $href }}"
    @if ($isExternal) target="_blank" rel="noopener noreferrer" @endif
    {{ $attributes->merge(['class' => "public-conversion-action $accentClass", 'data-animate' => 'fade-up']) }}
>
    <span class="public-conversion-action__icon">
        @switch($card['icon'])
            @case('upload')
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                @break
            @case('whatsapp')
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                @break
            @case('calendar')
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                @break
            @default
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        @endswitch
    </span>
    <span class="public-conversion-action__body">
        <span class="public-conversion-action__title">{{ $card['title'] }}</span>
        <span class="public-conversion-action__desc">{{ $card['description'] }}</span>
    </span>
    <span class="public-conversion-action__arrow" aria-hidden="true">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
    </span>
</a>
