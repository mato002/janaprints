@props([
    'action',
    'method' => 'POST',
])

@php
    $inFormModal = request()->header('Turbo-Frame') === 'erp-form-modal';
    $httpMethod = strtoupper($method);
@endphp

<form
    method="{{ $httpMethod === 'GET' ? 'GET' : 'POST' }}"
    action="{{ $action }}"
    @if (! $inFormModal) data-turbo-frame="_top" @endif
    {{ $attributes->merge(['class' => 'erp-form-shell']) }}
>
    @csrf
    @if ($inFormModal)
        <input type="hidden" name="_erp_modal" value="1">
        <input type="hidden" name="_erp_modal_return" value="{{ url()->current() }}">
    @endif
    @if (! in_array($httpMethod, ['GET', 'POST'], true))
        @method($method)
    @endif
    {{ $slot }}
</form>
