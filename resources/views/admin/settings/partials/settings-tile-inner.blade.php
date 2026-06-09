<div class="flex h-full min-w-0 gap-2.5">
    <span @class([
        'flex h-8 w-8 shrink-0 items-center justify-center rounded-md',
        'bg-erp-page text-slate-500 group-hover:bg-erp-accent/10 group-hover:text-erp-accent' => ! ($comingSoon ?? false),
        'bg-erp-page/80 text-slate-400' => ($comingSoon ?? false),
    ])>
        <x-admin.icon :name="$icon" class="h-4 w-4" />
    </span>

    <div class="min-w-0 flex-1">
        <h3 class="text-sm font-semibold leading-snug text-erp-primary group-hover:text-erp-accent [overflow-wrap:anywhere]">
            {{ $title }}
        </h3>

        @if ($statusLabel)
            <span class="mt-1 inline-flex max-w-full rounded px-1.5 py-0.5 text-[10px] font-medium leading-snug ring-1 ring-inset {{ $statusClasses }}">
                {{ $statusLabel }}
            </span>
        @endif

        <p class="mt-1.5 line-clamp-2 text-[11px] leading-snug text-slate-500">
            {{ $description }}
        </p>

        @if (isset($count) && $count !== null)
            <p class="mt-1.5 text-xs font-semibold tabular-nums text-erp-primary">
                {{ number_format($count) }}
            </p>
        @endif
    </div>
</div>
