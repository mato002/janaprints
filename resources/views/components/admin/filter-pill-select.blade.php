@props([
    'name',
    'label',
    'selected' => null,
])

@php
    $isActive = filled($selected) && ! in_array((string) $selected, ['', 'all'], true);
@endphp

<label class="erp-filter-pill-field relative inline-flex shrink-0">
    <span class="sr-only">{{ $label }}</span>
    <select
        name="{{ $name }}"
        aria-label="{{ $label }}"
        @class([
            'erp-filter-pill-select',
            'erp-filter-pill-select--active' => $isActive,
        ])
        {{ $attributes->except(['name', 'label', 'selected']) }}
    >
        {{ $slot }}
    </select>
    <x-admin.icon name="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 h-3 w-3 -translate-y-1/2 opacity-60" />
</label>
