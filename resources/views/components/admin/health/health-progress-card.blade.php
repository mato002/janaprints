@props([
    'label',
    'usedLabel',
    'freeLabel',
    'percent',
    'status' => null,
    'uploadsLabel' => null,
    'backupLabel' => null,
])

@php
    $pct = min(100, max(0, (float) $percent));

    $variant = match (true) {
        $pct >= 80 => 'danger',
        $pct >= 60 => 'warning',
        default => 'success',
    };

    $barClass = match ($variant) {
        'danger' => 'exec-progress__bar--danger',
        'warning' => 'exec-progress__bar--warning',
        default => 'exec-progress__bar--success',
    };
@endphp

<div {{ $attributes->merge(['class' => 'health-progress-card rounded-lg border border-erp-border bg-erp-card p-4 shadow-card']) }}>
    <x-admin.health.health-section-header :title="$label" :status="$status ?? $variant" />

    <div class="mt-4">
        <div class="mb-2 flex items-end justify-between gap-2">
            <span class="text-2xl font-bold tabular-nums text-erp-primary">{{ $usedLabel }}</span>
            <span class="text-sm font-semibold tabular-nums text-slate-600">{{ $pct }}% {{ __('Used') }}</span>
        </div>

        <div class="exec-progress__track" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
            <div class="exec-progress__bar {{ $barClass }}" style="width: {{ $pct }}%"></div>
        </div>

        <p class="mt-2 text-sm text-slate-600">
            <span class="font-medium text-erp-primary">{{ $freeLabel }}</span> {{ __('free') }}
        </p>
    </div>

    @if ($uploadsLabel || $backupLabel)
        <div class="mt-4 grid gap-2 border-t border-erp-border pt-3 sm:grid-cols-2">
            @if ($uploadsLabel)
                <div class="rounded-md bg-erp-page/60 px-3 py-2">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Uploads') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-erp-primary">{{ $uploadsLabel }}</p>
                </div>
            @endif
            @if ($backupLabel)
                <div class="rounded-md bg-erp-page/60 px-3 py-2">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Backups') }}</p>
                    <p class="mt-0.5 text-sm font-semibold text-erp-primary">{{ $backupLabel }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
