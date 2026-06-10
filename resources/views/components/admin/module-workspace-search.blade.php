@props([
    'moduleTitle',
    'moduleKey' => null,
])

<div
    x-data="moduleWorkspaceSearch(@js($moduleKey))"
    class="module-workspace-search workspace-search-bar"
>
    <div class="module-workspace-search__input-wrap relative w-full sm:w-56 lg:w-64">
        <x-admin.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
        <input
            type="search"
            x-model="query"
            @input="scheduleSearch()"
            @focus="open = true"
            @keydown.escape="clear()"
            class="erp-input module-workspace-search__input w-full py-1.5 pl-8 pr-3 text-sm"
            placeholder="{{ __('Search :module…', ['module' => $moduleTitle]) }}"
            autocomplete="off"
            aria-label="{{ __('Search module features') }}"
        >
    </div>

    <div
        x-show="open && query.trim()"
        x-cloak
        class="module-workspace-search__dropdown absolute right-0 z-30 mt-1 max-h-56 w-full min-w-[16rem] overflow-y-auto rounded-lg border border-erp-border bg-erp-card shadow-lg sm:w-72"
    >
        <template x-if="loading">
            <p class="px-3 py-4 text-center text-sm text-slate-500">{{ __('Searching…') }}</p>
        </template>
        <template x-if="! loading">
            <div>
                <template x-for="hit in hits" :key="hit.id">
                    <a
                        :href="hit.url"
                        :data-turbo-frame="hit.turbo_frame || 'module-workspace-content'"
                        data-turbo-action="advance"
                        @click="clear()"
                        class="module-workspace-search__hit block border-b border-erp-border px-3 py-2 last:border-0 hover:bg-erp-page"
                    >
                        <span class="block text-sm font-medium text-erp-primary" x-text="hit.label"></span>
                        <span class="mt-0.5 block text-xs text-slate-500" x-text="hit.path"></span>
                        <span
                            x-show="hit.description"
                            class="mt-0.5 block text-xs text-slate-400"
                            x-text="hit.description"
                        ></span>
                    </a>
                </template>
                <p x-show="hits.length === 0" class="px-3 py-4 text-center text-sm text-slate-500">
                    {{ __('No results found.') }}
                </p>
            </div>
        </template>
    </div>
</div>
