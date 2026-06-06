@props([
    'id',
    'name',
    'label',
    'required' => false,
    'full' => false,
    'options' => [],
    'placeholder' => 'Select an option',
    'value' => '',
    'hint' => null,
])

@php
    $resolvedValue = old($name, $value);
    $hasValue = filled($resolvedValue);
@endphp

<div @class([
    'public-field-float',
    'public-field-float--select',
    'public-conversion-form__field',
    'public-conversion-form__field--full' => $full,
    'is-filled' => $hasValue,
])>
    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($required) required @endif
        {{ $attributes->except(['class']) }}
    >
        <option value="" disabled @selected(! $hasValue)>{{ $placeholder }}</option>
        @foreach ($options as $option)
            @if (is_array($option))
                <option value="{{ $option['value'] }}" @selected($resolvedValue === $option['value'])>{{ $option['label'] }}</option>
            @else
                <option value="{{ $option }}" @selected($resolvedValue === $option)>{{ $option }}</option>
            @endif
        @endforeach
    </select>

    <label for="{{ $id }}">
        {{ $label }}
        @if ($required)
            <span class="public-field-float__required" aria-hidden="true">*</span>
        @endif
    </label>

    @if ($hint)
        <p class="public-field-float__hint">{{ $hint }}</p>
    @endif
</div>
