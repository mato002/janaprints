@props([
    'label',
    'value',
    'hint' => null,
    'status' => null,
    'icon' => null,
])

@php
    $statusKey = $status
        ? (is_object($status) && enum_exists($status::class) ? $status->value : (string) $status)
        : null;

    $valueTone = match ($statusKey) {
        'critical', 'danger' => 'text-red-700',
        'warning' => 'text-amber-800',
        'healthy', 'success' => 'text-emerald-800',
        default => 'text-erp-primary',
    };
@endphp

<div {{ $attributes->merge(['class' => 'health-metric-card rounded-lg border border-erp-border bg-erp-card p-3 shadow-card transition-shadow hover:shadow-card-hover sm:p-4']) }}>
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
            <p class="mt-1.5 text-lg font-bold tabular-nums {{ $valueTone }}">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-erp-accent/10 text-erp-accent">
                <x-admin.icon :name="$icon" class="h-4 w-4" />
            </div>
        @endif
    </div>
    @if ($statusKey)
        <div class="mt-2.5 border-t border-erp-border/60 pt-2">
            <x-admin.health.health-status-badge :status="$statusKey" />
        </div>
    @endif
</div>
