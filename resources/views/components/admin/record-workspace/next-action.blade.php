@props([
    'label',
    'hint' => null,
    'tone' => 'accent',
    'when' => null,
    'reasons' => [],
])

<section class="rw-nba rw-nba--{{ $tone }}" aria-label="{{ __('Next best action') }}">
    <div class="rw-nba__main">
        <p class="rw-nba__eyebrow">
            {{ $when ?? __('Today') }}
        </p>
        <p class="rw-nba__title">{{ $label }}</p>
        @if ($hint)
            <p class="rw-nba__hint">{{ $hint }}</p>
        @endif
    </div>

    @if ($reasons !== [])
        <ul class="rw-nba__reasons" role="list">
            @foreach ($reasons as $reason)
                <li>{{ $reason }}</li>
            @endforeach
        </ul>
    @endif

    @isset($cta)
        <div class="rw-nba__cta">{{ $cta }}</div>
    @endisset
</section>
