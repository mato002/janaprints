<span @class([
    'flex h-7 w-7 shrink-0 items-center justify-center rounded-md',
    'bg-erp-page text-slate-500 group-hover:bg-erp-accent/10 group-hover:text-erp-accent' => ! ($comingSoon ?? false),
    'bg-erp-page/80 text-slate-400' => ($comingSoon ?? false),
])>
    <x-admin.icon :name="$icon" class="h-3.5 w-3.5" />
</span>

<div class="min-w-0 flex-1">
    <div class="flex items-center gap-2">
        <span class="truncate text-sm font-medium text-erp-primary group-hover:text-erp-accent">{{ $title }}</span>
        @if ($domainLabel)
            <span class="hidden shrink-0 text-[10px] text-slate-400 sm:inline">{{ $domainLabel }}</span>
        @endif
    </div>
    <p class="truncate text-[11px] text-slate-500">{{ $description }}</p>
</div>

@if ($statusLabel)
    <span class="shrink-0 rounded px-1.5 py-0.5 text-[10px] font-medium ring-1 ring-inset {{ $statusClasses }}">
        {{ $statusLabel }}
    </span>
@endif
