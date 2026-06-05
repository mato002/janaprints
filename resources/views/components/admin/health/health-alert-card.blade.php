@props([
    'alert',
])

@php
    $severity = $alert['severity'];
    $severityKey = is_object($severity) && enum_exists($severity::class) ? $severity->value : (string) $severity;

    $border = match ($severityKey) {
        'critical' => 'border-l-red-500 bg-red-50/30',
        'warning' => 'border-l-amber-500 bg-amber-50/30',
        default => 'border-l-slate-400 bg-slate-50/40',
    };

    $icon = match ($severityKey) {
        'critical' => 'x-circle',
        'warning' => 'exclamation',
        default => 'information-circle',
    };

    $meta = match ($alert['type'] ?? '') {
        'backup' => [
            'impact' => __('Disaster recovery may be unavailable.'),
            'action' => __('Configure backup schedules and verify retention.'),
        ],
        'database' => [
            'impact' => __('ERP data access and transactions may be affected.'),
            'action' => __('Verify database connectivity and apply pending migrations.'),
        ],
        'storage' => [
            'impact' => __('Uploads, logs, and backups may fail when disk is full.'),
            'action' => __('Free disk space or expand storage capacity.'),
        ],
        'queue' => [
            'impact' => __('Background jobs and notifications may be delayed.'),
            'action' => __('Review failed jobs and ensure queue workers are running.'),
        ],
        default => [
            'impact' => __('Operational risk detected in monitored infrastructure.'),
            'action' => __('Review the alert details and take corrective action.'),
        ],
    };
@endphp

<article {{ $attributes->merge(['class' => "health-alert-card rounded-lg border border-erp-border border-l-4 p-4 shadow-card {$border}"]) }}>
    <div class="flex flex-wrap items-start gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm">
            <x-admin.icon :name="$icon" class="h-5 w-5 text-erp-primary" />
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-bold uppercase tracking-wide text-erp-primary">{{ $alert['title'] }}</h3>
                <x-admin.health.health-status-badge :status="$severityKey" :label="$severityKey === 'critical' ? __('Critical') : ($severityKey === 'warning' ? __('Warning') : __('Info'))" />
            </div>
            <p class="mt-2 text-sm text-slate-700">{{ $alert['message'] }}</p>
            <dl class="mt-3 grid gap-2 text-xs sm:grid-cols-2">
                <div class="rounded-md bg-white/70 px-3 py-2">
                    <dt class="font-semibold uppercase tracking-wide text-slate-500">{{ __('Impact') }}</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $meta['impact'] }}</dd>
                </div>
                <div class="rounded-md bg-white/70 px-3 py-2">
                    <dt class="font-semibold uppercase tracking-wide text-slate-500">{{ __('Recommended Action') }}</dt>
                    <dd class="mt-0.5 text-slate-700">{{ $meta['action'] }}</dd>
                </div>
            </dl>
        </div>
    </div>
</article>
