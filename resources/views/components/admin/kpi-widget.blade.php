@props([
    'label',
    'value',
    'hint' => null,
    'trend' => null,
    'icon' => null,
])

<x-admin.card
    :padding="false"
    :hover="true"
    {{ $attributes->class(['erp-kpi relative overflow-hidden']) }}
>
    <div class="flex items-start justify-between gap-2 px-3 py-2.5">
        <div class="min-w-0">
            <p class="text-card-title text-erp-muted">{{ $label }}</p>
            <p class="mt-0.5 text-card-value text-erp-primary tabular-nums">{{ $value }}</p>
            @if ($hint)
                <p class="mt-0.5 text-[11px] leading-tight text-slate-400">{{ $hint }}</p>
            @endif
            @if ($trend)
                <p class="mt-1 text-[11px] font-medium leading-tight {{ $trend['positive'] ?? false ? 'text-erp-success' : 'text-slate-500' }}">{{ $trend['label'] }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-erp-accent/10 text-erp-accent">
                <x-admin.icon :name="$icon" class="h-3.5 w-3.5" />
            </div>
        @endif
    </div>
</x-admin.card>
