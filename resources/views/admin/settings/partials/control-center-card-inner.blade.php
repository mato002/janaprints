@php
    $statusClasses = match ($statusVariant ?? 'neutral') {
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'danger' => 'bg-red-50 text-red-700 ring-red-600/20',
        default => 'bg-slate-100 text-slate-600 ring-slate-500/10',
    };

    $label = $statusLabel ?? $status ?? null;
    $detail = $statusDetail ?? null;
@endphp

<div class="flex items-start justify-between gap-3">
    <span @class([
        'flex h-11 w-11 shrink-0 items-center justify-center rounded-lg transition-colors',
        'bg-erp-page text-slate-500 group-hover:bg-erp-accent/10 group-hover:text-erp-accent' => ! ($comingSoon ?? false),
        'bg-erp-page text-slate-400' => ($comingSoon ?? false),
    ])>
        <x-admin.icon :name="$icon" class="h-6 w-6" />
    </span>
    @if ($label)
        <div class="shrink-0 text-right">
            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ring-1 ring-inset {{ $statusClasses }}">
                {{ $label }}
            </span>
            @if ($detail)
                <p class="mt-1 max-w-[9rem] text-[10px] font-medium leading-snug text-slate-500">{{ $detail }}</p>
            @endif
        </div>
    @endif
</div>

<h3 class="mt-3 text-sm font-semibold text-erp-primary group-hover:text-erp-accent sm:mt-4 sm:text-base">
    {{ $title }}
</h3>
<p class="mt-1 line-clamp-2 text-sm leading-relaxed text-slate-500 max-sm:hidden">
    {{ $description }}
</p>

@if ($comingSoon ?? false)
    <p class="mt-3 text-xs font-medium text-slate-400">{{ __('Coming soon') }}</p>
@else
    <span class="mt-4 inline-flex items-center gap-1 text-xs font-medium text-erp-accent opacity-0 transition-opacity group-hover:opacity-100">
        {{ __('Configure') }}
        <x-admin.icon name="chevron-left" class="h-3 w-3 rotate-180" />
    </span>
@endif
