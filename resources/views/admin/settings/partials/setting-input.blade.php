@php
    $inputId = str_replace(['[', ']', '.'], '_', $name);
    $controlClass = 'erp-select w-full max-w-full text-sm';
    $fieldClass = 'erp-input w-full max-w-full text-sm';
@endphp

@switch($type)
    @case('boolean')
        <select id="{{ $inputId }}" name="{{ $name }}" class="{{ $controlClass }}">
            @if ($allowInherit ?? false)
                <option value="inherit" @selected($value === null)>{{ $placeholder ?? __('Inherit') }}</option>
            @endif
            <option value="1" @selected($value === true || $value === 1 || $value === '1')>{{ __('On') }}</option>
            <option value="0" @selected($value === false || $value === 0 || $value === '0')>{{ __('Off') }}</option>
        </select>
        @break

    @case('integer')
        <input
            id="{{ $inputId }}"
            type="number"
            name="{{ $name }}"
            value="{{ $value !== null ? $value : '' }}"
            placeholder="{{ $placeholder ?? '' }}"
            class="{{ $fieldClass }}"
        >
        @break

    @default
        <input
            id="{{ $inputId }}"
            type="text"
            name="{{ $name }}"
            value="{{ $value !== null ? $value : '' }}"
            placeholder="{{ $placeholder ?? '' }}"
            class="{{ $fieldClass }}"
        >
@endswitch
