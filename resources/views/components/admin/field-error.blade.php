@props(['name' => null, 'messages' => null])

@php
    $resolvedMessages = $messages;

    if ($resolvedMessages === null && $name !== null) {
        $resolvedMessages = $errors->get($name);
    }
@endphp

@if ($resolvedMessages)
    <x-input-error
        :messages="$resolvedMessages"
        {{ $attributes->merge(['class' => 'mt-1']) }}
        @if ($name) data-erp-field-error="{{ $name }}" @endif
    />
@endif
