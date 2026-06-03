<div class="flex h-full min-w-0 gap-2.5">
    <span @class([
        'flex h-8 w-8 shrink-0 items-center justify-center rounded-md',
        'bg-erp-page text-slate-500 group-hover:bg-erp-accent/10 group-hover:text-erp-accent' => ! ($comingSoon ?? false),
        'bg-erp-page/80 text-slate-400' => ($comingSoon ?? false),
    ])>
        <x-admin.icon :name="$icon" class="h-4 w-4" />
    </span>

    <div class="min-w-0 flex-1 overflow-hidden">
        <div class="flex min-w-0 items-start justify-between gap-1.5">
            <h3 class="min-w-0 truncate text-sm font-semibold leading-tight text-erp-primary group-hover:text-erp-accent">
                {{ $title }}
            </h3>
            @if ($statusLabel)
                <span class="max-w-[5.5rem] shrink-0 truncate rounded px-1.5 py-0.5 text-[10px] font-medium leading-none ring-1 ring-inset {{ $statusClasses }}">
                    {{ $statusLabel }}
                </span>
            @endif
        </div>

        <p class="mt-1 line-clamp-2 text-[11px] leading-snug text-slate-500">
            {{ $description }}
        </p>
    </div>
</div>
