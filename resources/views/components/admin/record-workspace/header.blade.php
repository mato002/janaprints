@props([
    'eyebrow' => null,
    'backUrl' => null,
    'backLabel' => null,
    'title',
    'subtitle' => null,
    'meta' => [],
    'metrics' => [],
])

<header class="rw-header">
    <div class="rw-header__top">
        @if ($backUrl)
            <a href="{{ $backUrl }}" class="rw-header__back" data-turbo-frame="erp-main">
                ← {{ $backLabel ?? __('Back') }}
            </a>
        @endif

        @if (isset($badges) || ! $slot->isEmpty())
            <div class="rw-header__badges">
                @isset($badges)
                    {{ $badges }}
                @else
                    {{ $slot }}
                @endisset
            </div>
        @endif
    </div>

    <div class="rw-header__body">
        <div class="rw-header__identity">
            @if ($eyebrow)
                <p class="rw-header__eyebrow">{{ $eyebrow }}</p>
            @endif

            <h1 class="rw-header__title">{{ $title }}</h1>

            @if ($subtitle)
                <p class="rw-header__subtitle">{{ $subtitle }}</p>
            @endif

            @if ($meta !== [])
                <div class="rw-header__meta">
                    @foreach ($meta as $item)
                        <span class="rw-header__meta-item">{{ $item }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($metrics !== [] || isset($metricsSlot))
            <dl class="rw-header__metrics">
                @isset($metricsSlot)
                    {{ $metricsSlot }}
                @else
                    @foreach ($metrics as $metric)
                        <div>
                            <dt>{{ $metric['label'] }}</dt>
                            <dd @class(['rw-header__metric-value', $metric['class'] ?? null])>{{ $metric['value'] }}</dd>
                        </div>
                    @endforeach
                @endisset
            </dl>
        @endif
    </div>
</header>
