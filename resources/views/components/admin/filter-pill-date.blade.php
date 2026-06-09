@props([
    'name',
    'label',
    'value' => null,
])

@php
    $isActive = filled($value);
@endphp

<label class="erp-filter-pill-field relative inline-flex shrink-0">
    <span class="sr-only">{{ $label }}</span>
    <input
        type="date"
        name="{{ $name }}"
        value="{{ $value }}"
        aria-label="{{ $label }}"
        @class([
            'erp-filter-pill-date',
            'erp-filter-pill-date--active' => $isActive,
        ])
        {{ $attributes->except(['name', 'label', 'value']) }}
    />
</label>
