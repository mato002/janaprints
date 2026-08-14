@php
    $editable = $editable ?? false;
    $initialModule = $initialModule ?? null;
    $roleName = $roleName ?? '';
@endphp

<div
    class="role-access-workspace space-y-3"
    x-data="roleAccessWorkspace(@js([
        'modules' => $access['modules'],
        'columns' => $access['columns'],
        'granted' => $access['granted'],
        'uncatalogued' => $access['uncatalogued'],
        'editable' => $editable,
        'initialModule' => $initialModule,
        'roleName' => $roleName,
    ]))"
>
    <template x-for="name in grantedList" :key="'g-'+name">
        <input type="hidden" name="permissions[]" :value="name">
    </template>
                <template x-for="name in uncatalogued" :key="'u-'+name">
        <input type="hidden" name="permissions[]" :value="name">
    </template>
    <input type="hidden" name="_module" :value="activeModule?.key || ''">

    <div x-show="!activeModule" class="space-y-3">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Module access') }}</h2>
            <p class="mt-0.5 text-sm text-slate-500">{{ __('Open one module at a time to manage its permissions.') }}</p>
        </div>

        <div class="relative max-w-md">
            <x-admin.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
            <input
                type="search"
                x-model="moduleQuery"
                class="erp-input w-full py-1.5 pl-8 text-sm"
                placeholder="{{ __('Search modules…') }}"
                aria-label="{{ __('Search modules') }}"
            >
        </div>

        <div class="overflow-hidden rounded-lg border border-erp-border bg-white">
            <table class="erp-table erp-table--grid w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Module') }}</th>
                        <th class="w-32">{{ __('Access') }}</th>
                        <th class="w-36">{{ __('Permissions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="module in filteredModules" :key="module.key">
                        <tr class="cursor-pointer transition-colors hover:bg-slate-50/80" @click="openModule(module.key)">
                            <td class="py-3 font-medium text-erp-primary" x-text="module.label"></td>
                            <td class="py-3">
                                <span x-show="moduleStatus(module) === 'full'" class="text-xs font-medium text-emerald-700">{{ __('Full') }}</span>
                                <span x-show="moduleStatus(module) === 'partial'" class="text-xs font-medium text-amber-700">{{ __('Partial') }}</span>
                                <span x-show="moduleStatus(module) === 'none'" class="text-slate-300">—</span>
                            </td>
                            <td class="py-3 tabular-nums text-slate-600">
                                <span x-show="module.total_count > 0" x-text="moduleCounts(module)"></span>
                                <span x-show="module.total_count === 0">—</span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeModule" x-cloak class="space-y-3">
        <button type="button" class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-erp-accent" @click="closeModule()">
            <span aria-hidden="true">←</span> {{ __('Module access') }}
        </button>

        <div class="overflow-hidden rounded-lg border border-erp-border bg-white">
            <div class="border-b border-erp-border px-4 py-3">
                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400" x-text="roleName"></p>
                <h3 class="text-lg font-semibold uppercase tracking-wide text-erp-primary" x-text="activeModule?.label"></h3>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-4 text-sm" x-show="editable">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" class="text-erp-accent focus:ring-erp-accent" name="role_module_access_level" value="none" :checked="accessLevelChoice === 'none'" @change="setAccessLevel('none')">
                            <span>{{ __('No access') }}</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" class="text-erp-accent focus:ring-erp-accent" name="role_module_access_level" value="custom" :checked="accessLevelChoice === 'custom' || accessLevelChoice === 'partial'" @change="setAccessLevel('custom')">
                            <span>{{ __('Custom access') }}</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" class="text-erp-accent focus:ring-erp-accent" name="role_module_access_level" value="full" :checked="accessLevelChoice === 'full'" @change="setAccessLevel('full')">
                            <span>{{ __('Full access') }}</span>
                        </label>
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <p class="text-xs tabular-nums text-slate-500" x-text="activeModule ? (moduleCounts(activeModule) + ' {{ __('enabled') }}') : ''"></p>
                        <div class="flex gap-2" x-show="editable && activeModule && !matrixLocked">
                            <button type="button" class="text-xs font-medium text-erp-accent hover:underline" @click="setAccessLevel('full')">{{ __('Enable all') }}</button>
                            <button type="button" class="text-xs font-medium text-slate-500 hover:underline" @click="setAccessLevel('none')">{{ __('Clear all') }}</button>
                        </div>
                    </div>
                </div>

                <div class="relative mt-3 max-w-md">
                    <x-admin.icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                    <input
                        type="search"
                        x-model="capabilityQuery"
                        class="erp-input w-full py-1.5 pl-8 text-sm"
                        placeholder="{{ __('Search permissions…') }}"
                        aria-label="{{ __('Search permissions') }}"
                    >
                </div>
            </div>

            <div
                class="overflow-x-auto p-3"
                :class="matrixLocked ? 'pointer-events-none opacity-50' : ''"
            >
                <div class="role-module-matrix min-w-[40rem]" :style="matrixGridStyle">
                    <div class="role-module-matrix__head contents">
                        <div class="role-module-matrix__cell role-module-matrix__cell--head">{{ __('Capability') }}</div>
                        <template x-for="column in columns" :key="'h-'+column.key">
                            <div class="role-module-matrix__cell role-module-matrix__cell--head role-module-matrix__cell--center" x-text="column.label"></div>
                        </template>
                    </div>

                    <template x-for="row in matrixRows" :key="row.rowKey">
                        <div class="contents">
                            <template x-if="row.type === 'section'">
                                <div class="role-module-matrix__section" :style="'grid-column: 1 / span ' + (columns.length + 1)" x-text="row.label"></div>
                            </template>

                            <template x-if="row.type === 'capability'">
                                <div class="contents">
                                    <div class="role-module-matrix__cell">
                                        <div class="flex items-start gap-2">
                                            <button
                                                type="button"
                                                class="mt-0.5 text-slate-400 hover:text-erp-accent"
                                                x-show="row.advanced.length"
                                                @click="toggleExpanded(row.key)"
                                            >
                                                <span x-text="isExpanded(row.key) ? '▾' : '▸'"></span>
                                            </button>
                                            <span class="font-medium text-erp-primary" x-text="row.label"></span>
                                        </div>
                                    </div>
                                    <template x-for="column in columns" :key="row.key + '-' + column.key">
                                        <div class="role-module-matrix__cell role-module-matrix__cell--center">
                                            <template x-if="row.cells[column.key]">
                                                <input
                                                    type="checkbox"
                                                    class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                                    :disabled="!editable || matrixLocked"
                                                    :checked="isGranted(row.cells[column.key])"
                                                    @change="setGranted(row.cells[column.key], $event.target.checked)"
                                                >
                                            </template>
                                            <span x-show="!row.cells[column.key]" class="text-slate-300">—</span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="row.type === 'advanced'">
                                <div class="role-module-matrix__advanced" :style="'grid-column: 1 / span ' + (columns.length + 1)">
                                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Advanced actions') }}</p>
                                    <ul class="grid gap-2 sm:grid-cols-2">
                                        <template x-for="action in row.advanced" :key="action.key">
                                            <li>
                                                <label class="inline-flex items-center gap-2 text-sm text-erp-primary">
                                                    <input
                                                        type="checkbox"
                                                        class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                                        :disabled="!editable || matrixLocked"
                                                        :checked="isGranted(action.permission)"
                                                        @change="setGranted(action.permission, $event.target.checked)"
                                                    >
                                                    <span x-text="action.label"></span>
                                                </label>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
