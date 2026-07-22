@php
    $operatorMode = (bool) ($operatorMode ?? false);
@endphp

<div
    x-show="selectedKey"
    x-cloak
    class="designer-desk-workspace mt-6"
>
    <div x-show="panelLoading" class="rounded-xl border border-erp-border bg-white px-6 py-16 text-center text-sm text-slate-500">
        {{ __('Loading workspace…') }}
    </div>

    <template x-if="panel && !panelLoading">
        <x-admin.artwork-preview-lightbox>
            <div class="overflow-hidden rounded-xl border border-violet-200 bg-white shadow-lg">
                {{-- Job banner --}}
                <div class="border-b border-violet-100 bg-gradient-to-r from-violet-50 via-white to-white px-5 py-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-violet-600">{{ __('Working on') }}</p>
                            <h2 class="font-mono text-2xl font-bold text-erp-primary" x-text="panel.header?.request_number"></h2>
                        </div>
                        <button type="button" class="erp-btn-secondary text-sm" @click="clearSelection()">{{ __('Back to queue') }}</button>
                    </div>
                    <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 text-sm sm:grid-cols-4">
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Customer') }}</dt>
                            <dd class="mt-0.5 font-semibold text-slate-900" x-text="panel.context?.customer ?? '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Product') }}</dt>
                            <dd class="mt-0.5 font-medium text-slate-800" x-text="panel.context?.product ?? panel.header?.title ?? '—'"></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Due') }}</dt>
                            <dd
                                class="mt-0.5 font-semibold"
                                :class="panel.header?.is_late ? 'text-rose-700' : (panel.header?.is_due_today ? 'text-amber-700' : 'text-slate-800')"
                                x-text="panel.header?.due_display ?? '—'"
                            ></dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Priority') }}</dt>
                            <dd class="mt-0.5">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                    :class="{
                                        'bg-rose-100 text-rose-800': panel.header?.priority_value === 'urgent',
                                        'bg-amber-100 text-amber-800': panel.header?.priority_value === 'high',
                                        'bg-blue-100 text-blue-800': panel.header?.priority_value === 'normal',
                                        'bg-slate-100 text-slate-700': panel.header?.priority_value === 'low',
                                    }"
                                    x-text="panel.header?.priority"
                                ></span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <template x-if="panel.guidance">
                    <div class="border-b border-blue-100 bg-blue-50 px-5 py-3 text-sm text-blue-900" x-text="panel.guidance"></div>
                </template>

                <div class="grid grid-cols-1 gap-0 lg:grid-cols-2">
                    {{-- Customer files --}}
                    <section id="designer-desk-files" class="border-b border-erp-border p-5 lg:border-b-0 lg:border-r">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">{{ __('Customer Files') }}</h3>
                        <template x-if="!(panel.files?.customer?.length)">
                            <p class="mb-3 text-sm text-slate-500">{{ __('No files uploaded yet.') }}</p>
                        </template>
                        <ul class="mb-4 space-y-1.5">
                            <template x-for="file in panel.files?.customer ?? []" :key="file.id">
                                <li class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm">
                                    <span class="font-medium text-slate-800" x-text="file.name"></span>
                                    <a x-show="file.download_url" :href="file.download_url" class="text-xs font-semibold text-erp-accent hover:underline" download>{{ __('Download') }}</a>
                                </li>
                            </template>
                        </ul>

                        <div id="designer-desk-upload">
                            <template x-if="panel.files?.can_upload_version">
                                <form
                                    :action="panel.files.upload_version_url"
                                    method="POST"
                                    enctype="multipart/form-data"
                                    class="rounded-lg border-2 border-dashed border-violet-200 bg-violet-50/40 p-4"
                                    @if ($operatorMode) data-erp-desk-form @endif
                                >
                                    <input type="hidden" name="_token" :value="csrf">
                                    @if ($operatorMode)
                                        <input type="hidden" name="from" value="designer-desk">
                                    @endif
                                    <p class="mb-2 text-sm font-semibold text-violet-900">+ {{ __('Upload Artwork') }}</p>
                                    <input type="file" name="file" class="erp-input mb-2 w-full" accept=".pdf,.ai,.psd,.cdr,.svg,.png,.jpg,.jpeg" required>
                                    <input type="text" name="notes" class="erp-input mb-2 w-full text-sm" placeholder="{{ __('Version notes') }}">
                                    <button type="submit" class="erp-btn-primary w-full text-sm">{{ __('Upload version') }}</button>
                                </form>
                            </template>
                        </div>
                    </section>

                    {{-- Specifications --}}
                    <section class="border-b border-erp-border p-5">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">{{ __('Specifications') }}</h3>
                        <template x-if="!(panel.specifications?.length)">
                            <p class="text-sm text-slate-500">{{ __('No print specification linked.') }}</p>
                        </template>
                        <dl class="grid grid-cols-2 gap-3 text-sm">
                            <template x-for="spec in panel.specifications ?? []" :key="spec.label">
                                <div class="rounded-lg bg-slate-50 px-3 py-2">
                                    <dt class="text-[10px] font-semibold uppercase tracking-wide text-slate-400" x-text="spec.label"></dt>
                                    <dd class="mt-0.5 font-medium text-slate-800" x-text="spec.value"></dd>
                                </div>
                            </template>
                        </dl>
                    </section>

                    {{-- Version history --}}
                    <section class="border-b border-erp-border p-5 lg:border-b-0 lg:border-r">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">{{ __('Version History') }}</h3>
                        <template x-if="!(panel.files?.versions?.length)">
                            <p class="text-sm text-slate-500">{{ __('No versions yet.') }}</p>
                        </template>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="version in panel.files?.versions ?? []" :key="version.number">
                                <div
                                    class="flex min-w-[5rem] flex-col items-center rounded-lg border px-3 py-2 text-center"
                                    :class="version.is_current ? 'border-violet-300 bg-violet-50' : 'border-slate-200 bg-white'"
                                >
                                    <span class="text-sm font-bold text-erp-primary" x-text="'V' + version.number"></span>
                                    <div class="mt-1 flex gap-1">
                                        <button
                                            x-show="version.previewable"
                                            type="button"
                                            class="text-[10px] font-semibold text-erp-accent"
                                            :data-preview-url="version.preview_url"
                                            :data-preview-title="version.name"
                                            :data-preview-pdf="version.is_pdf ? '1' : '0'"
                                            @click="show($el.dataset.previewUrl, $el.dataset.previewTitle, $el.dataset.previewPdf === '1')"
                                        >{{ __('View') }}</button>
                                        <a x-show="version.download_url" :href="version.download_url" class="text-[10px] font-semibold text-slate-500" download>↓</a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </section>

                    {{-- Revision notes --}}
                    <section class="border-b border-erp-border p-5">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">{{ __('Revision Notes') }}</h3>
                        <div class="space-y-3 text-sm">
                            <template x-if="panel.revision_notes?.customer?.length">
                                <div>
                                    <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">{{ __('Customer') }}</p>
                                    <template x-for="(note, idx) in panel.revision_notes.customer" :key="'c-' + idx">
                                        <p class="rounded-lg bg-amber-50 px-3 py-2 text-slate-700" x-text="note"></p>
                                    </template>
                                </div>
                            </template>
                            <template x-if="panel.revision_notes?.sales?.length">
                                <div>
                                    <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-blue-700">{{ __('Sales') }}</p>
                                    <template x-for="(note, idx) in panel.revision_notes.sales" :key="'s-' + idx">
                                        <p class="rounded-lg bg-blue-50 px-3 py-2 text-slate-700" x-text="note"></p>
                                    </template>
                                </div>
                            </template>
                            <template x-if="panel.revision_notes?.internal?.length">
                                <div>
                                    <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ __('Internal') }}</p>
                                    <template x-for="(note, idx) in panel.revision_notes.internal" :key="'i-' + idx">
                                        <p class="rounded-lg bg-slate-50 px-3 py-2 text-slate-700" x-text="note"></p>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!(panel.revision_notes?.customer?.length || panel.revision_notes?.sales?.length || panel.revision_notes?.internal?.length)">
                                <p class="text-slate-500">{{ __('No revision notes.') }}</p>
                            </template>
                        </div>
                    </section>

                    {{-- Production checklist --}}
                    <section class="col-span-1 p-5 lg:col-span-2 lg:border-t lg:border-erp-border">
                        <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-500">{{ __('Production Checklist') }}</h3>
                        <ul class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
                            <template x-for="(check, idx) in panel.readiness ?? []" :key="idx">
                                <li class="flex items-center gap-2 rounded-lg border border-slate-100 px-3 py-2 text-sm">
                                    <span
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded text-xs font-bold"
                                        :class="check.done ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400'"
                                        x-text="check.done ? '☑' : '☐'"
                                    ></span>
                                    <span :class="check.done ? 'text-slate-800' : 'text-slate-500'" x-text="check.label"></span>
                                </li>
                            </template>
                        </ul>
                    </section>
                </div>

                {{-- Primary actions --}}
                <div class="flex flex-wrap gap-2 border-t border-erp-border bg-slate-50 px-5 py-4">
                    <template x-for="(action, idx) in panel.primary_actions ?? []" :key="idx">
                        <template x-if="action.type === 'post'">
                            <form :action="action.url" method="POST" @if ($operatorMode) data-erp-desk-form @endif>
                                <input type="hidden" name="_token" :value="csrf">
                                @if ($operatorMode)
                                    <input type="hidden" name="from" value="designer-desk">
                                @endif
                                <button
                                    type="submit"
                                    class="designer-desk-action-btn"
                                    :class="action.variant === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary'"
                                    x-text="action.label"
                                ></button>
                            </form>
                        </template>
                        <template x-if="action.type === 'scroll'">
                            <button
                                type="button"
                                class="designer-desk-action-btn erp-btn-secondary"
                                @click="scrollToSection(action.target)"
                                x-text="action.label"
                            ></button>
                        </template>
                        <template x-if="action.type === 'badge'">
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-800" x-text="action.label"></span>
                        </template>
                    </template>
                </div>
            </div>
        </x-admin.artwork-preview-lightbox>
    </template>
</div>
