@props([
    'title',
    'subtitle' => null,
    'status' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-start justify-between gap-3']) }}>
    <div class="min-w-0">
        <h2 class="text-xs font-bold uppercase tracking-wider text-erp-primary">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-0.5 text-xs text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
    @if ($status)
        <x-admin.health.health-status-badge :status="$status" />
    @endif
</div>
