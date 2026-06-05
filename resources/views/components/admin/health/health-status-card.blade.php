@props([
    'label',
    'status' => 'unknown',
    'value',
    'detail' => null,
    'icon' => null,
])

@php
    $statusKey = is_object($status) && enum_exists($status::class) ? $status->value : (string) $status;

    $accent = match ($statusKey) {
        'healthy', 'success' => 'border-emerald-500/80 bg-emerald-50/40',
        'warning', 'pending' => 'border-amber-500/80 bg-amber-50/40',
        'critical', 'danger' => 'border-red-500/80 bg-red-50/40',
        default => 'border-slate-300 bg-slate-50/60',
    };

    $dot = match ($statusKey) {
        'healthy', 'success' => 'bg-emerald-500',
        'warning', 'pending' => 'bg-amber-500',
        'critical', 'danger' => 'bg-red-500',
        default => 'bg-slate-400',
    };
@endphp

<div {{ $attributes->merge(['class' => "health-status-card rounded-lg border border-erp-border border-l-4 shadow-card {$accent}"]) }}>
    <div class="flex items-start justify-between gap-2 p-3 sm:p-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $dot }}" aria-hidden="true"></span>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600">{{ $label }}</p>
            </div>
            <p class="mt-1.5 text-sm font-bold text-erp-primary">{{ $value }}</p>
            @if ($detail)
                <p class="mt-0.5 text-xs text-slate-500">{{ $detail }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/80 text-erp-accent shadow-sm">
                <x-admin.icon :name="$icon" class="h-4 w-4" />
            </div>
        @endif
    </div>
</div>
