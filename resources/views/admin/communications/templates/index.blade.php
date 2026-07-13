<x-admin-layout :title="__('Communication Templates')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Templates')]]">
    <div
        class="communication-templates-workspace min-w-0"
        x-data="communicationTemplatesWorkspace(@js($bootstrap))"
        @keydown.escape.window="closePanels()"
    >
        <x-admin.page-header
            :title="__('Communication Templates')"
            :description="__('Reusable message templates for SMS, email, WhatsApp, and in-app notifications used across customer journeys and campaigns.')"
        >
            <x-slot:actions>
                <x-admin.crm-btn
                    type="button"
                    variant="primary"
                    x-show="can.create"
                    x-cloak
                    @click="openEditor()"
                >
                    <x-slot:icon>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </x-slot:icon>
                    {{ __('New template') }}
                </x-admin.crm-btn>
            </x-slot:actions>
        </x-admin.page-header>

        <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 xl:grid-cols-5">
            <div class="rounded-lg border border-erp-border bg-erp-card p-4 shadow-card">
                <p class="text-card-title text-erp-primary">{{ __('Total templates') }}</p>
                <p class="mt-1.5 text-card-value text-erp-primary tabular-nums" x-text="templates.length"></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-erp-card p-4 shadow-card">
                <p class="text-card-title text-erp-primary">{{ __('SMS templates') }}</p>
                <p class="mt-1.5 text-card-value text-erp-primary tabular-nums" x-text="templates.filter(t => t.channel === 'sms').length"></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-erp-card p-4 shadow-card">
                <p class="text-card-title text-erp-primary">{{ __('Email templates') }}</p>
                <p class="mt-1.5 text-card-value text-erp-primary tabular-nums" x-text="templates.filter(t => t.channel === 'email').length"></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-erp-card p-4 shadow-card">
                <p class="text-card-title text-erp-primary">{{ __('WhatsApp templates') }}</p>
                <p class="mt-1.5 text-card-value text-erp-primary tabular-nums" x-text="templates.filter(t => t.channel === 'whatsapp').length"></p>
            </div>
            <div class="rounded-lg border border-erp-border bg-erp-card p-4 shadow-card">
                <p class="text-card-title text-erp-primary">{{ __('Inactive templates') }}</p>
                <p class="mt-1.5 text-card-value text-erp-primary tabular-nums" x-text="templates.filter(t => t.status === 'inactive').length"></p>
            </div>
        </div>

        <div class="erp-card mb-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="workspace-segment" role="group" aria-label="{{ __('View mode') }}">
                    <button
                        type="button"
                        class="workspace-segment__btn"
                        :class="viewMode === 'list' ? 'workspace-segment__btn--active' : 'workspace-segment__btn--inactive'"
                        @click="viewMode = 'list'"
                    >
                        {{ __('List view') }}
                    </button>
                    <button
                        type="button"
                        class="workspace-segment__btn"
                        :class="viewMode === 'category' ? 'workspace-segment__btn--active' : 'workspace-segment__btn--inactive'"
                        @click="viewMode = 'category'"
                    >
                        {{ __('Category view') }}
                    </button>
                </div>

                <div class="flex flex-wrap items-end gap-2">
                    <div>
                        <label class="erp-label text-xs">{{ __('Channel') }}</label>
                        <select class="erp-input erp-input--sm" x-model="filters.channel">
                            <option value="">{{ __('All') }}</option>
                            <template x-for="opt in options.channels" :key="opt.value">
                                <option :value="opt.value" x-text="opt.label"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="erp-label text-xs">{{ __('Status') }}</label>
                        <select class="erp-input erp-input--sm" x-model="filters.status">
                            <option value="">{{ __('All') }}</option>
                            <template x-for="opt in options.statuses" :key="opt.value">
                                <option :value="opt.value" x-text="opt.label"></option>
                            </template>
                        </select>
                    </div>
                    <button
                        type="button"
                        class="erp-btn erp-btn--ghost erp-btn--sm"
                        x-show="hasActiveFilters"
                        x-cloak
                        @click="clearFilters()"
                    >{{ __('Clear') }}</button>
                </div>
            </div>
        </div>

        <template x-if="viewMode === 'category'">
            <div class="erp-card mb-4">
                <h2 class="erp-card-title">{{ __('Templates by category group') }}</h2>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    <template x-for="group in categoryGroupCards" :key="group.key">
                        <button
                            type="button"
                            class="rounded-lg border border-erp-border bg-white px-3 py-2.5 text-left transition-colors hover:border-erp-accent/40"
                            :class="filters.group === group.key ? 'ring-1 ring-erp-accent/30 border-erp-accent' : ''"
                            @click="toggleGroup(group.key)"
                        >
                            <p class="text-sm font-semibold text-erp-primary" x-text="group.label"></p>
                            <p class="mt-1 text-lg font-semibold tabular-nums text-erp-primary" x-text="group.count"></p>
                        </button>
                    </template>
                </div>
            </div>
        </template>

        <div class="erp-card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="erp-table w-full min-w-[56rem]">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Channel') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Version') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="template in filteredTemplates" :key="template.id">
                            <tr class="hover:bg-slate-50/80">
                                <td class="font-medium text-erp-primary" x-text="template.name"></td>
                                <td class="font-mono text-xs text-slate-600" x-text="template.code"></td>
                                <td x-text="template.channel_label"></td>
                                <td x-text="template.category_label"></td>
                                <td x-text="template.template_type_label"></td>
                                <td class="tabular-nums" x-text="'v' + template.version_number"></td>
                                <td>
                                    <span
                                        class="erp-badge"
                                        :class="template.status === 'active' ? 'erp-badge--success' : (template.status === 'inactive' ? 'erp-badge--neutral' : 'erp-badge--warning')"
                                        x-text="template.status_label"
                                    ></span>
                                </td>
                                <td class="text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-1">
                                        <button
                                            type="button"
                                            class="erp-btn erp-btn--ghost erp-btn--xs"
                                            x-show="can.edit"
                                            @click="openEditor(template)"
                                        >{{ __('Edit') }}</button>
                                        <button
                                            type="button"
                                            class="erp-btn erp-btn--ghost erp-btn--xs"
                                            x-show="can.versionView"
                                            @click="openVersions(template)"
                                        >{{ __('History') }}</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredTemplates.length === 0">
                            <td colspan="8" class="py-10 text-center text-slate-500">
                                <span x-show="templates.length === 0">{{ __('No templates yet. Create your first reusable communication template.') }}</span>
                                <span x-show="templates.length > 0">{{ __('No templates match the selected filters.') }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Editor modal --}}
        <div
            x-show="editorOpen"
            x-cloak
            class="fixed inset-0 z-[60] flex items-end justify-center overflow-y-auto p-4 sm:items-center sm:p-6"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-erp-primary/50 backdrop-blur-[1px]" @click="closeEditor()"></div>
            <div class="relative z-10 flex w-full max-w-lg max-h-[calc(100vh-2rem)] flex-col overflow-hidden rounded-xl border border-erp-border bg-white shadow-2xl" @click.stop>
                <div class="flex shrink-0 items-center justify-between gap-4 border-b border-erp-border px-5 py-4">
                    <h2 class="text-lg font-semibold text-erp-primary" x-text="editorMode === 'create' ? @js(__('New template')) : @js(__('Edit template'))"></h2>
                    <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" @click="closeEditor()" aria-label="{{ __('Close') }}">
                        <x-admin.icon name="x-mark" class="h-4 w-4" />
                    </button>
                </div>
                <form class="flex min-h-0 flex-1 flex-col overflow-hidden" @submit.prevent="saveTemplate()">
                    <div class="flex-1 space-y-3 overflow-y-auto px-5 py-4">
                        <div>
                            <label class="erp-label">{{ __('Name') }}</label>
                            <input type="text" class="erp-input w-full" x-model="form.name" required>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="erp-label">{{ __('Channel') }}</label>
                                <select class="erp-input w-full" x-model="form.channel" required>
                                    <template x-for="opt in options.channels" :key="opt.value">
                                        <option :value="opt.value" x-text="opt.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="erp-label">{{ __('Type') }}</label>
                                <select class="erp-input w-full" x-model="form.template_type" required>
                                    <template x-for="opt in options.types" :key="opt.value">
                                        <option :value="opt.value" x-text="opt.label"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="erp-label">{{ __('Category') }}</label>
                            <select class="erp-input w-full" x-model="form.category" required>
                                <template x-for="opt in options.categories" :key="opt.value">
                                    <option :value="opt.value" x-text="opt.label"></option>
                                </template>
                            </select>
                        </div>
                        <div x-show="['email', 'notification'].includes(form.channel)">
                            <label class="erp-label">{{ __('Subject') }}</label>
                            <input type="text" class="erp-input w-full" x-model="form.subject">
                        </div>
                        <div>
                            <label class="erp-label">{{ __('Body') }}</label>
                            <textarea class="erp-input w-full font-mono text-sm" rows="8" x-model="form.body" required></textarea>
                            <p class="mt-1 text-xs text-slate-500">{{ __('Use placeholders like') }} @{{customer_name}}</p>
                        </div>
                        <div x-show="editorMode === 'edit'" class="space-y-3">
                            <div>
                                <label class="erp-label">{{ __('Status') }}</label>
                                <select class="erp-input w-full" x-model="form.status">
                                    <template x-for="opt in options.statuses" :key="opt.value">
                                        <option :value="opt.value" x-text="opt.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="erp-label">{{ __('Description') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                                <textarea class="erp-input w-full" rows="2" x-model="form.description"></textarea>
                            </div>
                            <div>
                                <label class="erp-label">{{ __('Change notes') }}</label>
                                <input type="text" class="erp-input w-full" x-model="form.change_notes" :placeholder="@js(__('What changed in this version?'))">
                            </div>
                        </div>
                        <p class="text-sm text-red-600" x-show="editorError" x-text="editorError"></p>
                    </div>
                    <div class="flex shrink-0 justify-end gap-3 border-t border-erp-border px-5 py-4">
                        <button type="button" class="erp-btn-secondary" @click="closeEditor()">{{ __('Cancel') }}</button>
                        <button type="submit" class="erp-btn-primary" :disabled="editorSaving">
                            <span x-show="!editorSaving">{{ __('Save template') }}</span>
                            <span x-show="editorSaving">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Version history modal --}}
        <div
            x-show="versionsOpen"
            x-cloak
            class="fixed inset-0 z-[60] flex items-end justify-center overflow-y-auto p-4 sm:items-center sm:p-6"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-erp-primary/50 backdrop-blur-[1px]" @click="versionsOpen = false"></div>
            <div class="relative z-10 flex w-full max-w-xl max-h-[calc(100vh-2rem)] flex-col overflow-hidden rounded-xl border border-erp-border bg-white shadow-2xl" @click.stop>
                <div class="flex shrink-0 items-center justify-between gap-4 border-b border-erp-border px-5 py-4">
                    <h2 class="text-lg font-semibold text-erp-primary">{{ __('Version history') }}</h2>
                    <button type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" @click="versionsOpen = false" aria-label="{{ __('Close') }}">
                        <x-admin.icon name="x-mark" class="h-4 w-4" />
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <p class="text-xs text-slate-500 mb-3" x-show="versionsLoading">{{ __('Loading versions…') }}</p>
                    <template x-for="version in versions" :key="version.id">
                        <div class="mb-3 rounded-lg border border-erp-border p-3">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-semibold text-erp-primary" x-text="'Version ' + version.version_number"></p>
                                <span class="text-xs text-slate-500" x-text="formatDate(version.created_at)"></span>
                            </div>
                            <p class="mt-1 text-xs text-slate-600" x-text="version.change_notes || '—'"></p>
                            <p class="mt-1 text-xs text-slate-500" x-text="version.created_by"></p>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="comparePick(version, 'left')" x-text="compareLeft?.id === version.id ? @js(__('Left ✓')) : @js(__('Compare left'))"></button>
                                <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="comparePick(version, 'right')" x-text="compareRight?.id === version.id ? @js(__('Right ✓')) : @js(__('Compare right'))"></button>
                                <button type="button" class="erp-btn erp-btn--primary erp-btn--xs" x-show="can.restore" @click="restoreVersion(version)">{{ __('Restore') }}</button>
                            </div>
                        </div>
                    </template>
                    <template x-if="compareResult">
                        <div class="mt-4 rounded-lg border border-erp-accent/30 bg-erp-accent/5 p-3 text-sm">
                            <h3 class="font-semibold text-erp-primary">{{ __('Comparison') }}</h3>
                            <template x-for="(field, key) in compareResult.diff" :key="key">
                                <div class="mt-2" x-show="field.changed">
                                    <p class="text-xs font-semibold uppercase text-slate-500" x-text="key"></p>
                                    <p class="text-xs text-red-700 line-through" x-text="field.left || '—'"></p>
                                    <p class="text-xs text-emerald-800" x-text="field.right || '—'"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
