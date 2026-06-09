@props([
    'name' => 'erp-confirm',
    'title' => __('Confirm action'),
    'message' => '',
    'confirmLabel' => __('Confirm'),
    'cancelLabel' => __('Cancel'),
    'variant' => 'danger',
])

@php
    $confirmClass = $variant === 'danger' ? 'erp-btn-danger' : 'erp-btn-primary';
@endphp

<x-modal :name="$name" focusable maxWidth="md">
    <div class="p-6">
        <h2 class="text-lg font-semibold text-erp-primary">{{ $title }}</h2>
        @if ($message !== '')
            <p class="mt-2 text-sm text-slate-600">{{ $message }}</p>
        @endif
        @if (isset($body))
            <div class="mt-3 text-sm text-slate-600">{{ $body }}</div>
        @endif
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="erp-btn-secondary" x-on:click="$dispatch('close-modal', '{{ $name }}')">
                {{ $cancelLabel }}
            </button>
            @isset($confirm)
                {{ $confirm }}
            @else
                <button type="button" @class([$confirmClass]) data-erp-confirm-action>
                    {{ $confirmLabel }}
                </button>
            @endisset
        </div>
    </div>
</x-modal>
