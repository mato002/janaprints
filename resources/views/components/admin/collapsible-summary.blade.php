@props([
    'title' => null,
    'hint' => null,
    'open' => false,
])

@php
    $title = $title ?? __('Summary');
    $hint = $hint ?? __('Unfold to view dashboard cards');
@endphp

<details {{ $attributes->class(['erp-summary-fold']) }} @if ($open) open @endif>
    <summary class="erp-summary-fold__summary">
        <span class="erp-summary-fold__title">{{ $title }}</span>
        @if ($hint !== '')
            <span class="erp-summary-fold__hint">{{ $hint }}</span>
        @endif
        <span class="erp-summary-fold__chevron" aria-hidden="true"></span>
    </summary>
    <div class="erp-summary-fold__body">
        {{ $slot }}
    </div>
</details>
