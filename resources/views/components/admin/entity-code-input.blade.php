@props([
    'record' => null,
    'maxlength' => 50,
    'class' => 'block mt-1 w-full',
    'erp' => false,
])

@php
    $isEdit = filled($record);
@endphp

@if ($erp)
    <label @class(['erp-label', 'required' => $isEdit]) for="code">{{ __('Code') }}<x-admin.required-star :required="$isEdit" /></label>
    <input
        id="code"
        name="code"
        class="erp-input w-full"
        maxlength="{{ $maxlength }}"
        value="{{ old('code', $record?->code) }}"
        @if (! $isEdit) placeholder="{{ __('Auto-generated') }}" @endif
        @if ($isEdit) required @endif
    />
@else
    <x-input-label for="code" :value="__('Code')" :required="$isEdit" />
    <x-text-input
        id="code"
        name="code"
        :class="$class"
        :value="old('code', $record?->code)"
        :maxlength="$maxlength"
        :placeholder="$isEdit ? null : __('Auto-generated')"
        :required="$isEdit"
    />
@endif
