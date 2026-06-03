@props([
    'variant' => 'default',
])

@php
    $classes = match ($variant) {
        'success', 'active' => 'bg-green-50 text-erp-success ring-green-600/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'danger', 'inactive' => 'bg-red-50 text-erp-danger ring-red-600/20',
        'info' => 'bg-sky-50 text-erp-info ring-sky-600/20',
        'neutral' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-500/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {$classes}"]) }}>
    {{ $slot }}
</span>
