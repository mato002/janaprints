@props([
    'capability',
    'reversed' => false,
    'trustPoints' => [],
])

<article
    id="capability-{{ $capability['slug'] }}"
    @class([
        'public-capability',
        'public-capability--reversed' => $reversed,
    ])
    data-animate="fade-up"
>
    <div class="public-container">
        <div class="public-capability__grid capability-mobile-card">
            {{-- Visual --}}
            <div class="public-capability__visual capability-image-wrap" data-animate="{{ $reversed ? 'fade-left' : 'fade-right' }}">
                <div class="public-capability__visual-frame">
                    <x-public.media-image
                        :slot-key="'services.'.$capability['slug']"
                        :src="$capability['image']"
                        :alt="$capability['alt']"
                        fallback-key="stationery"
                        class="public-capability__image aspect-[4/3] w-full object-cover"
                    />
                    <div class="capability-image-fade" aria-hidden="true"></div>
                    <div class="public-capability__visual-accent bg-gradient-to-br {{ $capability['accent'] }}"></div>
                </div>
                <span class="public-capability__number">{{ $capability['number'] }}</span>
            </div>

            {{-- Content --}}
            <div class="public-capability__content capability-content">
                <span class="public-capability__badge bg-gradient-to-r {{ $capability['accent'] }}">
                    Capability {{ $capability['number'] }}
                </span>

                <h3 class="public-capability__title">{{ $capability['title'] }}</h3>
                <p class="public-capability__desc">{{ $capability['description'] }}</p>

                {{-- What we produce --}}
                <div class="public-capability__items">
                    <p class="public-capability__items-label">What we produce</p>
                    <ul class="public-capability__items-list">
                        @foreach ($capability['items'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>

                {{-- Service highlights --}}
                <dl class="public-capability__highlights">
                    @foreach ($capability['highlights'] as $highlight)
                        @if ($highlight['label'] !== 'What we produce')
                            <div class="public-capability__highlight">
                                <dt>{{ $highlight['label'] }}</dt>
                                <dd>{{ $highlight['value'] }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>

                {{-- Micro trust --}}
                <ul class="public-capability__trust">
                    @foreach ($trustPoints as $point)
                        <li>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>

                {{-- Actions --}}
                <div class="public-capability__actions">
                    <x-public.button href="{{ $quoteFormHref }}" variant="primary" class="max-md:hidden">
                        Request Quote
                    </x-public.button>
                    <x-public.button
                        href="#recent-work"
                        variant="ghost-dark"
                    >
                        View Related Work
                    </x-public.button>
                </div>
            </div>
        </div>
    </div>
</article>
