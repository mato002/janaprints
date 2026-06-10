@props([
    'cancelUrl' => null,
])

@php
    $inFormModal = request()->header('Turbo-Frame') === 'erp-form-modal';
@endphp

<div class="erp-form-modal__actions">
    @if ($inFormModal)
        <button type="button" class="erp-btn-secondary" data-erp-form-modal-close>
            {{ __('Cancel') }}
        </button>
    @elseif ($cancelUrl)
        <a href="{{ $cancelUrl }}" class="erp-btn-secondary">{{ __('Cancel') }}</a>
    @endif
    {{ $slot }}
</div>
