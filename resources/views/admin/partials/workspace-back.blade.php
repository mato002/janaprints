@if (! empty($workspaceNavigation['show_back']) && ! empty($workspaceNavigation['parent_url']))
    <div @class(['mb-1' => ! empty($compact), 'mb-3' => empty($compact)])>
        <a
            href="{{ $workspaceNavigation['parent_url'] }}"
            data-turbo-frame="erp-main"
            data-turbo-action="advance"
            @class([
                'inline-flex items-center gap-1.5 font-medium text-slate-600 transition hover:text-erp-accent',
                'rounded-md border border-erp-border bg-white px-2 py-1 text-xs shadow-sm hover:border-erp-accent/40 hover:bg-slate-50' => ! empty($compact),
                'gap-2 rounded-lg border border-erp-border bg-white px-3 py-2 text-sm shadow-sm hover:border-erp-accent/40 hover:bg-slate-50' => empty($compact),
            ])
        >
            <x-admin.icon name="chevron-left" class="h-4 w-4 shrink-0" />
            <span class="sm:hidden">{{ __('Back') }}</span>
            <span class="hidden sm:inline">{{ __('Back to :workspace', ['workspace' => $workspaceNavigation['parent_workspace_title']]) }}</span>
        </a>
    </div>
@endif
