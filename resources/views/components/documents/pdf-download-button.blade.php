@props([
    'url',
    'filename' => null,
    'label' => null,
])

@php
    $label ??= __('Download PDF');
    $filename ??= 'document';
@endphp

<button
    type="button"
    data-document-pdf-download
    data-document-pdf-download-url="{{ $url }}"
    data-document-pdf-download-filename="{{ $filename }}"
    {{ $attributes->merge(['class' => 'erp-btn-secondary']) }}
    aria-busy="false"
>
    <span data-document-pdf-download-label class="inline-flex items-center gap-2">
        {{ $label }}
    </span>
    <span data-document-pdf-download-loading class="hidden items-center gap-2">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        {{ __('Downloading…') }}
    </span>
</button>
