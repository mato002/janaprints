@props(['label', 'value', 'suffix' => null])

<div class="rounded-lg border border-erp-border bg-erp-card p-4 shadow-card transition-shadow duration-200 hover:shadow-card-hover">
    <p class="text-card-title text-erp-primary">{{ $label }}</p>
    <p class="mt-1.5 text-card-value text-erp-primary tabular-nums">
        {{ $value }}@if ($suffix)<span class="ml-1 text-base font-medium text-slate-400">{{ $suffix }}</span>@endif
    </p>
</div>
