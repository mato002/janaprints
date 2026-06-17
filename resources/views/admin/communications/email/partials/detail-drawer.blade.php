<div
    x-cloak
    x-show="drawerOpen"
    class="fixed inset-0 z-40 flex justify-end bg-slate-900/40"
    @click.self="closeDrawer()"
    @keydown.escape.window="closeDrawer()"
>
    <div class="flex h-full w-full max-w-xl flex-col bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-erp-border px-5 py-4">
            <div>
                <h2 class="text-lg font-semibold text-erp-primary">{{ __('Email detail') }}</h2>
                <p class="text-sm text-slate-500" x-text="detail?.subject ?? ''"></p>
            </div>
            <button type="button" class="erp-btn-secondary text-sm" @click="closeDrawer()">{{ __('Close') }}</button>
        </div>
        <div class="flex-1 overflow-y-auto px-5 py-4">
            <template x-if="loading">
                <p class="text-sm text-slate-500">{{ __('Loading…') }}</p>
            </template>
            <template x-if="!loading && detail">
                <div class="space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</p>
                            <p class="mt-1 font-medium" x-text="detail.status_label"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Retry count') }}</p>
                            <p class="mt-1" x-text="detail.retry_count"></p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Sender') }}</p>
                        <p class="mt-1" x-text="(detail.sender?.name ? detail.sender.name + ' ' : '') + '<' + (detail.sender?.email ?? '') + '>'"></p>
                        <p class="text-xs text-slate-500" x-text="detail.sender?.provider ?? ''"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Recipients') }}</p>
                        <p class="mt-1" x-text="(detail.recipients?.to ?? []).map(r => r.email).join(', ')"></p>
                    </div>
                    <template x-if="detail.related_entity">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Related entity') }}</p>
                            <template x-if="detail.related_entity.url">
                                <a :href="detail.related_entity.url" class="mt-1 inline-flex text-erp-accent" data-turbo-frame="erp-main" x-text="detail.related_entity.type + ' · ' + detail.related_entity.label"></a>
                            </template>
                            <template x-if="!detail.related_entity.url">
                                <p class="mt-1" x-text="detail.related_entity.type + ' · ' + detail.related_entity.label"></p>
                            </template>
                        </div>
                    </template>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Created') }}</p>
                            <p class="mt-1" x-text="detail.created_at_formatted ?? '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Sent') }}</p>
                            <p class="mt-1" x-text="detail.sent_at_formatted ?? '—'"></p>
                        </div>
                    </div>
                    <template x-if="detail.failed_at_formatted">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Failed') }}</p>
                            <p class="mt-1" x-text="detail.failed_at_formatted"></p>
                        </div>
                    </template>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Module') }}</p>
                            <p class="mt-1" x-text="detail.module ?? '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Entity') }}</p>
                            <p class="mt-1" x-text="detail.document_number ?? (detail.entity_type ? detail.entity_type + ' #' + detail.entity_id : '—')"></p>
                        </div>
                    </div>
                    <template x-if="detail.failure_reason">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Failure reason') }}</p>
                            <p class="mt-1 text-red-600" x-text="detail.failure_reason"></p>
                        </div>
                    </template>
                    <template x-if="(detail.attachments ?? []).length">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Attachments') }}</p>
                            <ul class="mt-2 list-disc pl-5">
                                <template x-for="attachment in detail.attachments" :key="attachment.label">
                                    <li x-text="attachment.label"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Audit timeline') }}</p>
                        <ul class="mt-2 space-y-2">
                            <template x-for="(event, index) in detail.audit_timeline" :key="index">
                                <li class="rounded border border-erp-border px-3 py-2">
                                    <p class="font-medium" x-text="event.event"></p>
                                    <p class="text-xs text-slate-500" x-text="(event.created_at ?? '') + (event.actor ? ' · ' + event.actor : '')"></p>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>
