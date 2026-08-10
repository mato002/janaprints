@props([
    'name' => 'file',
    'accept' => null,
    'label' => null,
    'hint' => null,
    'required' => false,
    'inputClass' => 'artwork-detail-file-upload__input',
])

@php
    $uploadLabel = $label ?? __('Choose file');
    $uploadHint = $hint ?? __('PDF, AI, PSD, PNG, JPG…');
@endphp

<div
    x-data="{ fileName: '' }"
    {{ $attributes->merge(['class' => 'artwork-detail-file-upload']) }}
    :class="{ 'artwork-detail-file-upload--has-file': fileName !== '' }"
    @click="$refs.fileInput.click()"
    @keydown.enter.prevent="$refs.fileInput.click()"
    @keydown.space.prevent="$refs.fileInput.click()"
    role="button"
    tabindex="0"
>
    <input
        x-ref="fileInput"
        type="file"
        name="{{ $name }}"
        @if ($accept) accept="{{ $accept }}" @endif
        @if ($required) required @endif
        class="{{ $inputClass }}"
        @change="fileName = $event.target.files?.[0]?.name ?? ''"
    >
    <svg class="artwork-detail-file-upload__icon h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
    </svg>
    <p class="artwork-detail-file-upload__label" x-show="!fileName">{{ $uploadLabel }}</p>
    <p class="artwork-detail-file-upload__hint" x-show="!fileName">{{ $uploadHint }}</p>
    <p class="artwork-detail-file-upload__name" x-show="fileName" x-text="fileName"></p>
</div>
