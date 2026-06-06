@php
    $next = $workspace['next_action'];
@endphp

<section class="qr-360__next-action qr-360__next-action--{{ $next['tone'] }}" aria-label="{{ __('Next recommended action') }}">
    <div>
        <p class="qr-360__next-label">{{ __('Next Recommended Action') }}</p>
        <p class="qr-360__next-title">{{ $next['label'] }}</p>
        <p class="qr-360__next-hint">{{ $next['hint'] }}</p>
    </div>
</section>
