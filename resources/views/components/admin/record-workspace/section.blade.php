@props([
    'title' => null,
    'tone' => 'default', // default | work | edit | muted
    'flush' => false,
])

<section {{ $attributes->class([
    'rw-section',
    'rw-section--'.$tone,
    'rw-section--flush' => $flush,
]) }}>
    @if ($title || isset($actions))
        <div class="rw-section__head">
            @if ($title)
                <h2 class="rw-section__title">{{ $title }}</h2>
            @endif
            @isset($actions)
                <div class="rw-section__actions">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="rw-section__body">
        {{ $slot }}
    </div>
</section>
