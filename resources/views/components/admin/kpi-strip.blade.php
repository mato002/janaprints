@props([])

@if (trim($slot) !== '')
    <div {{ $attributes->merge(['class' => 'module-kpi-strip mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6']) }}>
        {{ $slot }}
    </div>
@endif
