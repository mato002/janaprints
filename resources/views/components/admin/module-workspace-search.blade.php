@props([
    'moduleTitle',
    'featureIndex' => [],
])

<div
    x-data="moduleWorkspaceSearch(@js($featureIndex))"
    class="module-workspace-search"
>
    <div class="relative max-w-md">
        <x-admin.icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        <input
            type="search"
            x-model="query"
            @focus="open = true"
            @keydown.escape="clear()"
            class="erp-input w-full py-2 pl-9 pr-4 text-sm"
            placeholder="{{ __('Search :module…', ['module' => $moduleTitle]) }}"
            autocomplete="off"
            aria-label="{{ __('Search module features') }}"
        >
    </div>

    <div
        x-show="open && query.trim()"
        x-cloak
        class="mt-2 max-h-64 overflow-y-auto rounded-lg border border-erp-border bg-erp-card shadow-lg"
    >
        <template x-for="hit in hits" :key="hit.id">
            <a
                :href="hit.url"
                data-turbo-frame="module-workspace-content"
                data-turbo-action="advance"
                @click="clear()"
                class="block border-b border-erp-border px-4 py-3 last:border-0 hover:bg-erp-page"
            >
                <span class="block text-sm font-medium text-erp-primary" x-text="hit.label"></span>
                <span class="mt-0.5 block text-xs text-slate-500" x-text="hit.path"></span>
                <span
                    x-show="hit.description"
                    class="mt-1 block text-xs text-slate-400"
                    x-text="hit.description"
                ></span>
            </a>
        </template>
        <p x-show="hits.length === 0" class="px-4 py-6 text-center text-sm text-slate-500">
            {{ __('No matches in this module.') }}
        </p>
    </div>
</div>
