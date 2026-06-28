@props([
    'title',
    'breadcrumbs' => [],
    'maxWidth' => '2xl',
])

@php
    $inFormModal = request()->header('Turbo-Frame') === 'erp-form-modal';
    $modalMaxWidth = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '3xl' => 'sm:max-w-3xl',
        '4xl' => 'sm:max-w-4xl',
        '5xl' => 'sm:max-w-5xl',
        default => 'sm:max-w-2xl',
    };
@endphp

@if ($inFormModal)
    <div @class(['erp-form-modal mx-auto w-full max-w-[calc(100vw-2rem)]', $modalMaxWidth]) data-erp-form-modal-panel>
        <div class="erp-form-modal__header">
            <h2 id="erp-form-modal-title" class="erp-form-modal__title">{{ $title }}</h2>
            <button
                type="button"
                class="erp-form-modal__close"
                data-erp-form-modal-close
                aria-label="{{ __('Close') }}"
            >
                <x-admin.icon name="x-mark" class="h-5 w-5" />
            </button>
        </div>
        <div class="erp-form-modal__body">
            {{ $slot }}
        </div>
    </div>
@else
    <x-admin-layout :title="$title" :breadcrumbs="$breadcrumbs">
        <div @class([
            'bg-white shadow rounded-lg p-6',
            'max-w-sm' => $maxWidth === 'sm',
            'max-w-md' => $maxWidth === 'md',
            'max-w-lg' => $maxWidth === 'lg',
            'max-w-xl' => $maxWidth === 'xl',
            'max-w-2xl' => $maxWidth === '2xl',
            'max-w-3xl' => $maxWidth === '3xl',
            'max-w-4xl' => $maxWidth === '4xl',
            'max-w-5xl' => $maxWidth === '5xl',
        ])>
            {{ $slot }}
        </div>
    </x-admin-layout>
@endif
