@props([])

@if (trim($slot) !== '')
    <div {{ $attributes->merge(['class' => 'module-action-bar mb-4 flex flex-wrap items-center gap-2']) }}>
        {{ $slot }}
    </div>
@endif
