<x-admin-layout :title="__('Communication Templates')" :breadcrumbs="[['label' => __('Communications'), 'url' => route('admin.workspaces.communications')], ['label' => __('Templates')]]">
    <div
        class="communication-templates-workspace min-w-0"
        x-data="communicationTemplatesWorkspace(@js($bootstrap))"
        @keydown.escape.window="closePanels()"
    >
        <x-admin.page-header
            :title="__('Communication Templates')"
            :description="__('Template-driven messages for SMS, email, WhatsApp, and in-app notifications. Preview and version control only — no sending in this phase.')"
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
            <x-admin.stat-card :label="__('Total templates')" :value="$summary['total']" />
            <x-admin.stat-card :label="__('SMS templates')" :value="$summary['sms']" />
            <x-admin.stat-card :label="__('Email templates')" :value="$summary['email']" />
            <x-admin.stat-card :label="__('WhatsApp templates')" :value="$summary['whatsapp']" />
            <x-admin.stat-card :label="__('Inactive templates')" :value="$summary['inactive']" />
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

                <form method="GET" action="{{ route('admin.communications.templates.index') }}" class="flex flex-wrap items-end gap-2" data-turbo-frame="erp-main">
                    <div>
                        <label class="erp-label text-xs">{{ __('Channel') }}</label>
                        <select name="channel" class="erp-input erp-input--sm" onchange="this.form.submit()">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Enums\CommunicationChannel::cases() as $channel)
                                <option value="{{ $channel->value }}" @selected(request('channel') === $channel->value)>{{ $channel->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="erp-label text-xs">{{ __('Status') }}</label>
                        <select name="status" class="erp-input erp-input--sm" onchange="this.form.submit()">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Enums\CommunicationTemplateStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if (request()->hasAny(['channel', 'status', 'category', 'group']))
                        <x-admin.crm-btn variant="ghost" size="sm" :href="route('admin.communications.templates.index')" data-turbo-frame="erp-main">{{ __('Clear') }}</x-admin.crm-btn>
                    @endif
                </form>
            </div>
        </div>

        <template x-if="viewMode === 'category'">
            <div class="erp-card mb-4">
                <h2 class="erp-card-title">{{ __('Templates by category group') }}</h2>
                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    @foreach ($categoryGroups as $group)
                        <a
                            href="{{ route('admin.communications.templates.index', ['group' => $group['key']]) }}"
                            data-turbo-frame="erp-main"
                            class="rounded-lg border border-erp-border bg-white px-3 py-2.5 transition-colors hover:border-erp-accent/40 {{ request('group') === $group['key'] ? 'ring-1 ring-erp-accent/30 border-erp-accent' : '' }}"
                        >
                            <p class="text-sm font-semibold text-erp-primary">{{ $group['label'] }}</p>
                            <p class="mt-1 text-lg font-semibold tabular-nums text-erp-primary">{{ $group['count'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </template>

        <div class="grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-7">
                <div class="erp-card overflow-hidden p-0">
                    <div class="overflow-x-auto">
                        <table class="erp-table w-full min-w-[40rem]">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Channel') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Version') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($templates as $template)
                                    <tr
                                        class="cursor-pointer hover:bg-slate-50/80"
                                        :class="selectedId === {{ $template->id }} ? 'bg-erp-accent/5' : ''"
                                        @click="selectTemplate({{ $template->id }})"
                                    >
                                        <td class="font-medium text-erp-primary">{{ $template->name }}</td>
                                        <td class="font-mono text-xs text-slate-600">{{ $template->code }}</td>
                                        <td>{{ $template->channel->label() }}</td>
                                        <td>{{ $template->category->label() }}</td>
                                        <td class="tabular-nums">v{{ $template->version_number }}</td>
                                        <td>
                                            <span class="erp-badge {{ $template->status === \App\Enums\CommunicationTemplateStatus::Active ? 'erp-badge--success' : ($template->status === \App\Enums\CommunicationTemplateStatus::Inactive ? 'erp-badge--neutral' : 'erp-badge--warning') }}">
                                                {{ $template->status->label() }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click.stop="selectTemplate({{ $template->id }})">{{ __('Open') }}</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-500">
                                            {{ __('No templates yet. Create your first reusable communication template.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="space-y-4 xl:col-span-5">
                <div class="erp-card" x-show="selected" x-cloak>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h2 class="erp-card-title" x-text="selected?.name"></h2>
                            <p class="text-xs text-slate-500">
                                <span x-text="selected?.code"></span>
                                · <span x-text="selected?.channel_label"></span>
                                · <span x-text="'v' + (selected?.version_number ?? '')"></span>
                            </p>
                        </div>
                        <div class="flex gap-1">
                            <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" x-show="can.edit" @click="openEditor(selected)">{{ __('Edit') }}</button>
                            <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" x-show="can.versionView" @click="openVersions()">{{ __('History') }}</button>
                        </div>
                    </div>
                    <dl class="mt-3 grid grid-cols-2 gap-2 text-xs">
                        <div><dt class="text-slate-500">{{ __('Category') }}</dt><dd class="font-medium" x-text="selected?.category_label"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd class="font-medium" x-text="selected?.template_type_label"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd class="font-medium" x-text="selected?.status_label"></dd></div>
                        <div><dt class="text-slate-500">{{ __('Updated') }}</dt><dd class="font-medium" x-text="formatDate(selected?.updated_at)"></dd></div>
                    </dl>
                    <div class="mt-3" x-show="selected?.subject">
                        <p class="text-xs font-semibold text-slate-500">{{ __('Subject') }}</p>
                        <p class="mt-1 rounded border border-erp-border bg-slate-50 px-2 py-1.5 text-sm" x-text="selected?.subject"></p>
                    </div>
                    <div class="mt-3">
                        <p class="text-xs font-semibold text-slate-500">{{ __('Body') }}</p>
                        <pre class="mt-1 max-h-32 overflow-auto whitespace-pre-wrap rounded border border-erp-border bg-slate-50 px-2 py-1.5 text-sm" x-text="selected?.body"></pre>
                    </div>
                </div>

                <div class="erp-card" x-show="selected" x-cloak>
                    <h2 class="erp-card-title">{{ __('Preview') }}</h2>
                    <p class="text-xs text-slate-500 mb-3">{{ __('Provide sample data to render output. No messages are sent.') }}</p>
                    <div class="max-h-48 space-y-2 overflow-y-auto">
                        <template x-for="variable in variables" :key="variable.key">
                            <div>
                                <label class="erp-label text-xs" x-text="variable.label"></label>
                                <input type="text" class="erp-input erp-input--sm w-full" x-model="previewData[variable.key]">
                            </div>
                        </template>
                    </div>
                    <button type="button" class="erp-btn erp-btn--primary erp-btn--sm mt-3 w-full" @click="runPreview()" :disabled="previewLoading">
                        <span x-show="!previewLoading">{{ __('Render preview') }}</span>
                        <span x-show="previewLoading">{{ __('Rendering…') }}</span>
                    </button>
                    <template x-if="previewResult">
                        <div class="mt-3 space-y-2">
                            <template x-if="previewResult.subject">
                                <div>
                                    <p class="text-xs font-semibold text-slate-500">{{ __('Rendered subject') }}</p>
                                    <p class="mt-1 rounded border border-emerald-200 bg-emerald-50 px-2 py-1.5 text-sm" x-text="previewResult.subject"></p>
                                </div>
                            </template>
                            <div>
                                <p class="text-xs font-semibold text-slate-500">{{ __('Rendered body') }}</p>
                                <pre class="mt-1 whitespace-pre-wrap rounded border border-emerald-200 bg-emerald-50 px-2 py-1.5 text-sm" x-text="previewResult.body"></pre>
                            </div>
                            <template x-if="previewResult.validation?.missing?.length">
                                <p class="text-xs text-amber-800">{{ __('Missing variables') }}: <span x-text="previewResult.validation.missing.join(', ')"></span></p>
                            </template>
                        </div>
                    </template>
                </div>

                <div class="erp-card text-sm text-slate-500" x-show="!selected">
                    <p>{{ __('Select a template to inspect details, preview rendered output, or review version history.') }}</p>
                </div>
            </div>
        </div>

        {{-- Editor drawer --}}
        <div
            x-show="editorOpen"
            x-cloak
            class="fixed inset-0 z-40 flex justify-end bg-slate-900/30"
            @click.self="closeEditor()"
        >
            <div class="flex h-full w-full max-w-lg flex-col bg-white shadow-xl" @click.stop>
                <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
                    <h2 class="text-lg font-semibold text-erp-primary" x-text="editorMode === 'create' ? @js(__('New template')) : @js(__('Edit template'))"></h2>
                    <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="closeEditor()">{{ __('Close') }}</button>
                </div>
                <form class="flex flex-1 flex-col overflow-hidden" @submit.prevent="saveTemplate()">
                    <div class="flex-1 space-y-3 overflow-y-auto px-4 py-4">
                        <template x-if="editorMode === 'create'">
                            <div>
                                <label class="erp-label">{{ __('Code') }}</label>
                                <input type="text" class="erp-input w-full" x-model="form.code" required>
                            </div>
                        </template>
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
                        <div>
                            <label class="erp-label">{{ __('Status') }}</label>
                            <select class="erp-input w-full" x-model="form.status" required>
                                <template x-for="opt in options.statuses" :key="opt.value">
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
                        <div>
                            <label class="erp-label">{{ __('Description') }}</label>
                            <textarea class="erp-input w-full" rows="2" x-model="form.description"></textarea>
                        </div>
                        <div x-show="editorMode === 'edit'">
                            <label class="erp-label">{{ __('Change notes') }}</label>
                            <input type="text" class="erp-input w-full" x-model="form.change_notes" :placeholder="@js(__('What changed in this version?'))">
                        </div>
                        <p class="text-sm text-red-600" x-show="editorError" x-text="editorError"></p>
                    </div>
                    <div class="border-t border-erp-border px-4 py-3">
                        <button type="submit" class="erp-btn erp-btn--primary w-full" :disabled="editorSaving">
                            <span x-show="!editorSaving">{{ __('Save template') }}</span>
                            <span x-show="editorSaving">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Version history drawer --}}
        <div
            x-show="versionsOpen"
            x-cloak
            class="fixed inset-0 z-40 flex justify-end bg-slate-900/30"
            @click.self="versionsOpen = false"
        >
            <div class="flex h-full w-full max-w-xl flex-col bg-white shadow-xl" @click.stop>
                <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
                    <h2 class="text-lg font-semibold text-erp-primary">{{ __('Version history') }}</h2>
                    <button type="button" class="erp-btn erp-btn--ghost erp-btn--xs" @click="versionsOpen = false">{{ __('Close') }}</button>
                </div>
                <div class="flex-1 overflow-y-auto px-4 py-4">
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
