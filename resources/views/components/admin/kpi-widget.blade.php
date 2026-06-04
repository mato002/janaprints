@props([
    'label',
    'value',
    'hint' => null,
    'trend' => null,
    'icon' => null,
])

<x-admin.card :hover="true" class="relative overflow-hidden">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-card-title text-erp-primary">{{ $label }}</p>
            <p class="mt-1.5 text-card-value text-erp-primary tabular-nums">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
            @endif
            @if ($trend)
                <p class="mt-2 text-xs font-medium {{ $trend['positive'] ?? false ? 'text-erp-success' : 'text-slate-500' }}">{{ $trend['label'] }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-erp-accent/10 text-erp-accent">
                <x-admin.icon :name="$icon" class="w-5 h-5" />
            </div>
        @endif
    </div>
</x-admin.card>
