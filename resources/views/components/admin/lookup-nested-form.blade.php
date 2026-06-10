@props([
    'title',
    'action',
    'maxWidth' => '4xl',
    'enctype' => null,
])

@php
    $maxWidthClass = match ($maxWidth) {
        '5xl' => 'erp-lookup-modal--w-5xl',
        '4xl' => 'erp-lookup-modal--w-4xl',
        '3xl' => 'erp-lookup-modal--w-3xl',
        '2xl' => 'erp-lookup-modal--w-2xl',
        'md' => 'erp-lookup-modal--w-md',
        default => 'erp-lookup-modal--w-4xl',
    };
@endphp

<div
    class="erp-form-modal erp-lookup-modal w-full {{ $maxWidthClass }}"
    data-erp-lookup-modal-panel
>
    <div class="erp-form-modal__header">
        <h2 id="erp-lookup-modal-title" class="erp-form-modal__title">{{ $title }}</h2>
        <button type="button" class="erp-form-modal__close" data-erp-lookup-modal-close aria-label="{{ __('Close') }}">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
        </button>
    </div>
    <div class="erp-form-modal__body">
        <form
            method="POST"
            action="{{ $action }}"
            class="space-y-4"
            data-erp-lookup-form
            @if ($enctype) enctype="{{ $enctype }}" @endif
        >
            @csrf
            <input type="hidden" name="_erp_lookup_create" value="1">
            @include('admin.partials.lookup-validation-errors')
            {{ $slot }}
            <div class="erp-form-modal__actions !mt-4 !pt-4">
                <button type="button" class="erp-btn-secondary" data-erp-lookup-modal-close>{{ __('Cancel') }}</button>
                <x-primary-button>{{ __('Save') }}</x-primary-button>
            </div>
        </form>
    </div>
</div>
