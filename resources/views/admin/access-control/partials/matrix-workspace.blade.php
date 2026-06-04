{{-- Included with: workspace, editable, storageKey (optional) --}}
@php
    $editable = $editable ?? false;
    $storageKey = $storageKey ?? null;
    $workspaceConfig = array_merge($workspace, [
        'editable' => $editable,
        'storageKey' => $storageKey,
    ]);
@endphp

<div x-data="permissionMatrixWorkspace(@js($workspaceConfig))" class="permission-matrix-workspace">
    <div class="permission-matrix-chips-bar sticky top-0 z-30 border-b border-erp-border bg-erp-page/95 py-1.5 backdrop-blur-sm">
        <div class="permission-matrix-chips" role="tablist" aria-label="{{ __('Module filters') }}">
            <template x-for="module in modules" :key="module.key">
                <button
                    type="button"
                    role="tab"
                    @click="setModule(module.key)"
                    :aria-selected="activeModule === module.key"
                    :class="activeModule === module.key
                        ? 'erp-filter-pill erp-filter-pill--active'
                        : 'erp-filter-pill'"
                    x-text="module.label"
                ></button>
            </template>
        </div>
    </div>

    <div class="permission-matrix-toolbar flex flex-wrap items-center gap-2 border-b border-erp-border py-2">
        <p class="shrink-0 text-[11px] text-slate-500">
            <span x-text="activeModuleStats.capabilities"></span> {{ __('capabilities') }}
            <span class="text-slate-300">·</span>
            <span class="font-medium text-slate-700">
                <span x-text="activeModuleStats.permissionsEnabled"></span>/<span x-text="activeModuleStats.totalPermissions"></span>
            </span>
            {{ __('enabled') }}
        </p>

        <div class="relative min-w-[10rem] flex-1 sm:max-w-[12rem]">
            <input
                type="search"
                x-model="search"
                class="permission-matrix-search erp-input w-full pl-7"
                placeholder="{{ __('Search…') }}"
                autocomplete="off"
            >
            <svg class="pointer-events-none absolute left-2 top-1/2 h-3 w-3 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z" />
            </svg>
        </div>

        @if ($editable)
            <div class="flex min-w-0 flex-1 flex-wrap items-center gap-1 sm:justify-end">
                <button type="button" @click="bulkEnableModule()" class="permission-matrix-bulk-btn">{{ __('Enable module') }}</button>
                <button type="button" @click="bulkDisableModule()" class="permission-matrix-bulk-btn">{{ __('Disable module') }}</button>
                <span class="hidden h-3 w-px bg-erp-border sm:inline-block" aria-hidden="true"></span>
                <template x-for="column in bulkColumnActions" :key="column.key">
                    <button
                        type="button"
                        @click="bulkEnableColumn(column.key)"
                        class="permission-matrix-bulk-btn"
                        x-text="column.label"
                    ></button>
                </template>
            </div>
        @endif
    </div>

    <x-admin.card class="!p-0 overflow-hidden">
        <div
            class="permission-matrix-scroll max-h-[calc(100vh-13.5rem)] overflow-auto sm:max-h-[calc(100vh-12rem)]"
            @scroll.passive="onTableScroll($event)"
        >
            <div class="permission-matrix-grid min-w-max">
                <div
                    class="permission-matrix-grid-row permission-matrix-grid-head sticky top-0 z-20 bg-erp-page shadow-[0_1px_0_0_rgb(226_232_240)]"
                    :style="gridTemplateColumns"
                >
                    <div class="permission-matrix-sticky-col permission-matrix-sticky-corner font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('Capability') }}
                    </div>
                    <template x-for="column in activeColumns" :key="'head-' + column.key">
                        <div class="permission-matrix-head-cell" x-text="column.label"></div>
                    </template>
                </div>

                <template x-for="row in pagedRows" :key="row.key">
                    <div class="permission-matrix-grid-row hover:bg-slate-50/80" :style="gridTemplateColumns">
                        <div class="permission-matrix-sticky-col">
                            <span class="block truncate font-medium text-slate-700" x-text="row.label" :title="row.label"></span>
                        </div>
                        <template x-for="column in activeColumns" :key="row.key + '-' + column.key">
                            <div class="permission-matrix-cell">
                                <template x-if="row.cells[column.key]">
                                    <div class="flex justify-center">
                                        @if ($editable)
                                            <label class="inline-flex cursor-pointer items-center justify-center">
                                                <input
                                                    type="checkbox"
                                                    class="permission-matrix-check"
                                                    :checked="isGranted(row.cells[column.key])"
                                                    @change="setGranted(row.cells[column.key], $event.target.checked)"
                                                >
                                                <span class="sr-only" x-text="row.label + ' — ' + column.label"></span>
                                            </label>
                                        @else
                                            <span
                                                class="permission-matrix-status"
                                                :class="isGranted(row.cells[column.key]) ? 'is-granted' : 'is-denied'"
                                                x-text="isGranted(row.cells[column.key]) ? '✓' : '✗'"
                                            ></span>
                                        @endif
                                    </div>
                                </template>
                                <template x-if="! row.cells[column.key]">
                                    <span class="text-slate-300">—</span>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                <div x-show="visibleRows.length === 0" class="permission-matrix-empty">
                    {{ __('No capabilities match your filter.') }}
                </div>
                <div x-show="hasMoreRows" class="permission-matrix-more">
                    {{ __('Scroll for more…') }}
                </div>
            </div>
        </div>
    </x-admin.card>

    @if ($editable)
        <template x-for="permission in granted" :key="permission">
            <input type="hidden" name="permissions[]" :value="permission">
        </template>
        <template x-for="permission in uncatalogued" :key="'unc-' + permission">
            <input type="hidden" name="permissions[]" :value="permission">
        </template>

        <div x-show="uncatalogued.length > 0" class="mt-2 rounded-lg border border-dashed border-amber-300 bg-amber-50 px-3 py-2 text-xs text-amber-900">
            <p class="font-medium">{{ __('Additional system permissions preserved') }}</p>
            <div class="mt-1.5 flex flex-wrap gap-1">
                <template x-for="permission in uncatalogued" :key="'tag-' + permission">
                    <span class="inline-flex items-center rounded border border-amber-200 bg-white px-1.5 py-0.5 font-mono text-[10px]" x-text="permission"></span>
                </template>
            </div>
        </div>
    @endif
</div>
