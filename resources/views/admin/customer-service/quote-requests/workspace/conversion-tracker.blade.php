<section class="qr-360__card">
    <h2 class="qr-360__card-title">{{ __('Conversion Status') }}</h2>

    <ol class="qr-360__conversion" role="list">
        @foreach ($workspace['conversion'] as $step)
            <li class="qr-360__conversion-step {{ $step['linked'] ? 'qr-360__conversion-step--linked' : 'qr-360__conversion-step--pending' }}">
                <span class="qr-360__conversion-marker" aria-hidden="true">
                    @if ($step['linked'])
                        ✓
                    @else
                        ✗
                    @endif
                </span>
                <div class="qr-360__conversion-body">
                    <p class="qr-360__conversion-label">{{ $step['label'] }}</p>
                    <p class="qr-360__conversion-state">
                        @if ($step['linked'])
                            {{ $step['reference'] ?? __('Linked') }}
                        @else
                            {{ __('Not converted') }}
                        @endif
                    </p>
                </div>
                @if (! $step['linked'] && ! empty($step['url']))
                    <a href="{{ $step['url'] }}" class="qr-360__conversion-link" data-turbo-frame="erp-main">{{ __('Convert') }}</a>
                @endif
            </li>
        @endforeach
    </ol>
</section>
