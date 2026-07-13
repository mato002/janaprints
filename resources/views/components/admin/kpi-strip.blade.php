@props([])

@if (trim($slot) !== '')
    <div {{ $attributes->class(['module-kpi-strip']) }}>
        {{ $slot }}
    </div>
@endif
