<div class="settings-tile-inner flex h-full min-w-0 gap-2 sm:gap-2.5">
    <span @class([
        'flex h-7 w-7 shrink-0 items-center justify-center rounded-md sm:h-8 sm:w-8',
        'bg-erp-page text-slate-500 group-hover:bg-erp-accent/10 group-hover:text-erp-accent' => ! ($comingSoon ?? false),
        'bg-erp-page/80 text-slate-400' => ($comingSoon ?? false),
    ])>
        <x-admin.icon :name="$icon" class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
    </span>

    <div class="min-w-0 flex-1">
        <h3 class="text-xs font-semibold leading-snug text-erp-primary group-hover:text-erp-accent [overflow-wrap:anywhere] sm:text-sm">
            {{ $title }}
        </h3>

        @if ($statusLabel)
            <span class="mt-1 inline-flex max-w-full rounded px-1.5 py-0.5 text-[10px] font-medium leading-snug ring-1 ring-inset {{ $statusClasses }}">
                {{ $statusLabel }}
            </span>
        @endif

        <p class="settings-tile-desc mt-1.5 line-clamp-2 text-[11px] leading-snug text-slate-500">
            {{ $description }}
        </p>

        @if (isset($count) && $count !== null)
            <p class="mt-1.5 text-xs font-semibold tabular-nums text-erp-primary">
                {{ number_format($count) }}
            </p>
        @endif
    </div>
</div>
