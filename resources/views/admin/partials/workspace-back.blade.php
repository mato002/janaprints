@if (! empty($workspaceNavigation['show_back']) && ! empty($workspaceNavigation['parent_url']))
    <div class="mb-3">
        <a
            href="{{ $workspaceNavigation['parent_url'] }}"
            data-turbo-frame="erp-main"
            data-turbo-action="advance"
            class="inline-flex items-center gap-2 rounded-lg border border-erp-border bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:border-erp-accent/40 hover:bg-slate-50 hover:text-erp-accent"
        >
            <x-admin.icon name="chevron-left" class="h-4 w-4 shrink-0" />
            <span>{{ __('Back to :workspace', ['workspace' => $workspaceNavigation['parent_workspace_title']]) }}</span>
        </a>
    </div>
@endif
