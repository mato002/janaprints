@props([
    'count' => 0,
    'label' => null,
])

@php
    $label = $label ?? __('Filters');
@endphp

<div
    {{ $attributes->class(['erp-filter-sheet']) }}
    data-erp-filter-sheet
    x-data="erpFilterSheet({ count: {{ (int) $count }} })"
    @keydown.escape.window="open && close()"
>
    <div class="erp-filter-sheet__inline" x-ref="inline"></div>

    <button
        type="button"
        class="erp-filter-sheet__trigger"
        x-show="hasFields"
        x-cloak
        @click="toggle()"
        :aria-expanded="open.toString()"
        aria-haspopup="dialog"
        title="{{ $label }}"
    >
        <x-admin.icon name="filter" class="h-4 w-4 shrink-0" />
        <span class="erp-filter-sheet__trigger-label">{{ $label }}</span>
        <span
            class="erp-filter-sheet__badge"
            x-show="count > 0"
            x-cloak
            x-text="count"
        ></span>
    </button>

    <div
        class="erp-filter-sheet__layer"
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
    >
        <div class="erp-filter-sheet__overlay" @click="close()" aria-hidden="true"></div>

        <div
            class="erp-filter-sheet__panel"
            role="dialog"
            aria-modal="true"
            aria-label="{{ $label }}"
            @click.stop
            x-ref="panel"
        >
            <div class="erp-filter-sheet__handle" aria-hidden="true"></div>

            <header class="erp-filter-sheet__header">
                <h2 class="erp-filter-sheet__title">{{ $label }}</h2>
                <button
                    type="button"
                    class="erp-filter-sheet__close"
                    @click="close()"
                    aria-label="{{ __('Close') }}"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </header>

            <div class="erp-filter-sheet__body" x-ref="fields">
                {{ $slot }}
            </div>

            <footer class="erp-filter-sheet__footer">
                <button
                    type="button"
                    class="erp-btn-ghost text-sm"
                    data-erp-filter-reset
                    @click="close()"
                >{{ __('Reset') }}</button>
                <button
                    type="button"
                    class="erp-btn-primary text-sm"
                    @click="apply()"
                >{{ __('Apply filters') }}</button>
            </footer>
        </div>
    </div>
</div>
