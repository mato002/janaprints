@props([
    'percent' => 0,
    'variant' => 'neutral',
])

@php
    $barClasses = match ($variant) {
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'draft' => 'bg-slate-400',
        default => 'bg-slate-300',
    };
    $width = min(100, max(0, abs($percent)));
@endphp

<div class="flex min-w-[5rem] items-center gap-2">
    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
        <div class="{{ $barClasses }} h-full rounded-full transition-all" style="width: {{ $width }}%"></div>
    </div>
    <span class="w-12 shrink-0 text-right text-xs tabular-nums text-slate-600">{{ number_format($percent, 1) }}%</span>
</div>
