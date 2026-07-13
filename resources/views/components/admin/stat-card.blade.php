@props(['label', 'value', 'suffix' => null])

<div {{ $attributes->merge(['class' => 'erp-kpi rounded-lg border border-erp-border bg-erp-card px-3 py-2.5 shadow-card transition-shadow duration-200 hover:shadow-card-hover']) }}>
    <p class="text-card-title text-erp-muted">{{ $label }}</p>
    <p class="mt-0.5 text-card-value text-erp-primary tabular-nums">
        {{ $value }}@if ($suffix)<span class="ml-1 text-xs font-medium text-slate-400">{{ $suffix }}</span>@endif
    </p>
</div>
