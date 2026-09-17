@props([])

@if (trim($slot) !== '')
    <x-admin.collapsible-summary>
        <div {{ $attributes->class(['module-kpi-strip mb-0']) }}>
            {{ $slot }}
        </div>
    </x-admin.collapsible-summary>
@endif
