@props([
    'title' => null,
])

<section {{ $attributes->class(['rw-rail-card']) }}>
    @if ($title || isset($actions))
        <div class="rw-rail-card__head">
            @if ($title)
                <h2 class="rw-rail-card__title">{{ $title }}</h2>
            @endif
            @isset($actions)
                <div>{{ $actions }}</div>
            @endisset
        </div>
    @endif

    {{ $slot }}
</section>
