@php
    $statusClasses = match ($card['statusVariant'] ?? 'neutral') {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
    };

    $metrics = $card['metrics'];
    $isInteractive = ! ($card['comingSoon'] ?? false) && filled($card['href'] ?? null);
@endphp

<div class="flex items-start justify-between gap-3">
    <span @class([
        'flex h-10 w-10 shrink-0 items-center justify-center rounded-lg transition-colors',
        'bg-erp-page text-slate-500 group-hover:bg-erp-accent/10 group-hover:text-erp-accent' => $isInteractive,
        'bg-erp-page/80 text-slate-400' => ! $isInteractive,
    ])>
        <x-admin.icon :name="$card['icon']" class="h-5 w-5" />
    </span>

    <div class="shrink-0 text-right">
        <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 ring-inset {{ $statusClasses }}">
            {{ $card['statusLabel'] }}
        </span>
        <p class="mt-1 text-[10px] font-medium text-slate-400">{{ $card['category_label'] }}</p>
    </div>
</div>

<h3 @class([
    'mt-3 text-sm font-semibold leading-tight text-erp-primary',
    'group-hover:text-erp-accent' => $isInteractive,
])>
    {{ $card['title'] }}
</h3>

<p class="mt-1 line-clamp-2 flex-1 text-xs leading-relaxed text-slate-500">
    {{ $card['description'] }}
</p>

<div class="mt-3 grid grid-cols-2 gap-2 border-t border-erp-border/70 pt-3 sm:grid-cols-4">
    <div class="min-w-0">
        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">{{ __('Fields') }}</p>
        <p class="text-sm font-semibold tabular-nums text-erp-primary">{{ number_format($metrics['field_count']) }}</p>
    </div>
    <div class="min-w-0">
        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">{{ __('Required') }}</p>
        <p class="text-sm font-semibold tabular-nums text-emerald-700">{{ number_format($metrics['required_count']) }}</p>
    </div>
    <div class="min-w-0">
        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">{{ __('Read-only') }}</p>
        <p class="text-sm font-semibold tabular-nums text-slate-600">{{ number_format($metrics['read_only_count']) }}</p>
    </div>
    <div class="min-w-0">
        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">{{ __('Hidden') }}</p>
        <p class="text-sm font-semibold tabular-nums text-amber-700">{{ number_format($metrics['hidden_count']) }}</p>
    </div>
</div>

<div class="mt-3 flex items-center justify-between gap-2 border-t border-erp-border/70 pt-2">
    <p class="min-w-0 truncate text-[10px] text-slate-400">
        <span class="font-medium text-slate-500">{{ __('Updated') }}:</span>
        {{ $card['updated_label'] }}
    </p>

    @if ($card['has_governance_issues'] ?? false)
        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-medium text-amber-800 ring-1 ring-inset ring-amber-600/20">
            <x-admin.icon name="exclamation" class="h-3 w-3" />
            {{ __('Review') }}
        </span>
    @elseif ($isInteractive)
        <span class="inline-flex shrink-0 items-center gap-1 text-[10px] font-medium text-erp-accent opacity-0 transition-opacity group-hover:opacity-100">
            {{ __('Configure') }}
            <x-admin.icon name="chevron-left" class="h-3 w-3 rotate-180" />
        </span>
    @else
        <span class="shrink-0 text-[10px] font-medium text-slate-400">{{ __('Coming soon') }}</span>
    @endif
</div>
