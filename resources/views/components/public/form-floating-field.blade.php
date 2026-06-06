@props([
    'id',
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'optional' => false,
    'full' => false,
    'value' => '',
    'rows' => 4,
    'autocomplete' => null,
    'inputmode' => null,
    'maxlength' => null,
    'hint' => null,
])

@php
    $resolvedValue = old($name, $value);
    $hasValue = filled($resolvedValue);
@endphp

<div @class([
    'public-field-float',
    'public-conversion-form__field',
    'public-conversion-form__field--full' => $full,
    'is-filled' => $hasValue,
])>
    @if ($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            placeholder=" "
            @if ($required) required @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
            {{ $attributes->except(['class']) }}
        >{{ $resolvedValue }}</textarea>
    @else
        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $resolvedValue }}"
            placeholder=" "
            @if ($required) required @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($inputmode) inputmode="{{ $inputmode }}" @endif
            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
            {{ $attributes->except(['class']) }}
        >
    @endif

    <label for="{{ $id }}">
        {{ $label }}
        @if ($required)
            <span class="public-field-float__required" aria-hidden="true">*</span>
        @elseif ($optional)
            <span class="public-field-float__optional">(optional)</span>
        @endif
    </label>

    @if ($hint)
        <p class="public-field-float__hint">{{ $hint }}</p>
    @endif
</div>
