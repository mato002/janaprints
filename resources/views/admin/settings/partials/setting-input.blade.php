@php
    $inputId = str_replace(['[', ']', '.'], '_', $name);
@endphp

@switch($type)
    @case('boolean')
        <select id="{{ $inputId }}" name="{{ $name }}" class="erp-select w-full min-w-[10rem]">
            @if ($allowInherit ?? false)
                <option value="inherit" @selected($value === null)>{{ $placeholder ?? __('Inherit') }}</option>
            @endif
            <option value="1" @selected($value === true || $value === 1 || $value === '1')>{{ __('Yes') }}</option>
            <option value="0" @selected($value === false || $value === 0 || $value === '0')>{{ __('No') }}</option>
        </select>
        @break

    @case('integer')
        <input
            id="{{ $inputId }}"
            type="number"
            name="{{ $name }}"
            value="{{ $value !== null ? $value : '' }}"
            placeholder="{{ $placeholder ?? '' }}"
            class="erp-input w-full min-w-[10rem]"
        >
        @break

    @default
        <input
            id="{{ $inputId }}"
            type="text"
            name="{{ $name }}"
            value="{{ $value !== null ? $value : '' }}"
            placeholder="{{ $placeholder ?? '' }}"
            class="erp-input w-full min-w-[10rem]"
        >
@endswitch
