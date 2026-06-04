@props([
    'variant' => 'default',
])

@php
    $classes = match ($variant) {
        'success', 'active', 'completed', 'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning', 'pending', 'pending_approval' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'danger', 'inactive', 'rejected', 'cancelled' => 'bg-red-50 text-red-700 ring-red-600/20',
        'info', 'in_production', 'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
        'draft', 'neutral' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        default => 'bg-slate-100 text-slate-700 ring-slate-500/20',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {$classes}"]) }}>
    {{ $slot }}
</span>
