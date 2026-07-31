@php
    $operatorMode = (bool) ($operatorMode ?? false);
@endphp

<div
    x-show="panelOpen"
    x-cloak
    class="fixed inset-0 z-40 flex justify-end"
    @keydown.escape.window="closePanel()"
>
    <div class="absolute inset-0 bg-slate-900/30" @click="closePanel()"></div>
    <aside class="relative z-10 flex h-full w-full max-w-lg flex-col border-l border-erp-border bg-white shadow-xl production-operator-panel">
        <div class="flex items-center justify-between border-b border-erp-border px-4 py-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Job panel') }}</p>
                <h2 class="text-lg font-semibold text-erp-primary" x-text="panel?.header?.job_number ?? '—'"></h2>
                <p class="text-sm font-medium text-slate-700" x-text="panel?.header?.stage ?? ''"></p>
            </div>
            <button type="button" class="production-operator-btn erp-btn-secondary" @click="closePanel()">{{ __('Close') }}</button>
        </div>

        <div class="production-operator-sticky border-b border-erp-border bg-slate-50 px-4 py-3" x-show="panel?.operator_actions?.length">
            <div class="flex flex-wrap gap-2">
                <template x-for="(action, idx) in panel.operator_actions ?? []" :key="idx">
                    <template x-if="action.type === 'post'">
                        <form :action="action.url" method="POST" @if ($operatorMode) data-erp-desk-form @endif>
                            <input type="hidden" name="_token" :value="csrf">
                            @if ($operatorMode)
                                <input type="hidden" name="from" value="production-floor">
                            @endif
                            <button
                                type="submit"
                                class="production-operator-btn"
                                :class="action.variant === 'primary' ? 'erp-btn-primary' : (action.variant === 'ghost' ? 'erp-btn-ghost' : 'erp-btn-secondary')"
                                x-text="action.label"
                            ></button>
                        </form>
                    </template>
                    <template x-if="action.type === 'modal' || action.type === 'qc'">
                        <button
                            type="button"
                            class="production-operator-btn"
                            :class="action.variant === 'primary' ? 'erp-btn-primary' : (action.variant === 'ghost' ? 'erp-btn-ghost' : 'erp-btn-secondary')"
                            x-text="action.label"
                            @click="openActionModal(panel.job.public_id, action.target ?? 'qc')"
                        ></button>
                    </template>
                    <template x-if="action.type === 'panel'">
                        <button
                            type="button"
                            class="production-operator-btn"
                            :class="action.variant === 'primary' ? 'erp-btn-primary' : (action.variant === 'ghost' ? 'erp-btn-ghost' : 'erp-btn-secondary')"
                            x-text="action.label"
                            @click="openActionModal(panel.job.public_id, action.target ?? 'machine')"
                        ></button>
                    </template>
                    <template x-if="action.type === 'link'">
                        <a
                            :href="action.url"
                            class="production-operator-btn erp-btn-secondary"
                            @if ($operatorMode) data-erp-modal-open @else data-turbo-frame="erp-main" @endif
                            x-text="action.label"
                        ></a>
                    </template>
                </template>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4" x-show="!panelLoading">
            <template x-if="panel">
                <div class="space-y-4">
                    <section class="production-floor-panel-hero">
                        <dl>
                            <div class="sm:col-span-2">
                                <dt class="text-slate-500">{{ __('Customer') }}</dt>
                                <dd class="text-base font-semibold text-erp-primary" x-text="panel.header.customer ?? '—'"></dd>
                            </div>
                            <div class="sm:col-span-2" x-show="panel.header?.sales_order_number">
                                <dt class="text-slate-500">{{ __('Sales order') }}</dt>
                                <dd>
                                    <a
                                        x-show="panel.links?.sales_order"
                                        :href="panel.links.sales_order"
                                        class="font-mono text-sm font-medium text-erp-accent underline decoration-erp-accent/40 underline-offset-2 hover:decoration-erp-accent"
                                        @if ($operatorMode) data-erp-modal-open @else data-turbo-frame="erp-main" data-turbo-action="advance" @endif
                                        x-text="panel.header.sales_order_number"
                                    ></a>
                                    <span
                                        class="font-mono text-sm font-medium"
                                        x-show="!panel.links?.sales_order"
                                        x-text="panel.header.sales_order_number"
                                    ></span>
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-slate-500">{{ __('Product') }}</dt>
                                <dd class="font-medium" x-text="panel.header.product ?? '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">{{ __('Current stage') }}</dt>
                                <dd class="font-medium" x-text="panel.header.stage ?? '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">{{ __('Due date') }}</dt>
                                <dd class="font-medium" x-text="panel.header.required_date ?? '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">{{ __('Machine') }}</dt>
                                <dd x-text="panel.job?.machine ?? '—'"></dd>
                            </div>
                            <div>
                                <dt class="text-slate-500">{{ __('Work center') }}</dt>
                                <dd x-text="panel.job?.work_center ?? '—'"></dd>
                            </div>
                        </dl>
                    </section>

                    <div class="production-floor-panel-status-grid">
                        <div class="production-floor-panel-status-card">
                            <dt>{{ __('Production status') }}</dt>
                            <dd x-text="panel.header.status ?? '—'"></dd>
                        </div>
                        <div class="production-floor-panel-status-card">
                            <dt>{{ __('Artwork / job status') }}</dt>
                            <dd x-text="panel.job?.status_label ?? '—'"></dd>
                        </div>
                        <div class="production-floor-panel-status-card">
                            <dt>{{ __('Material / fulfilment') }}</dt>
                            <dd x-text="panel.fulfilment?.status_label ?? '—'"></dd>
                        </div>
                        <div class="production-floor-panel-status-card">
                            <dt>{{ __('Quality / handoff') }}</dt>
                            <dd x-text="panel.header?.status ?? '—'"></dd>
                        </div>
                        <div class="production-floor-panel-status-card">
                            <dt>{{ __('Vendor status') }}</dt>
                            <dd x-text="panel.outsource?.vendor?.vendor_name ?? @js(__('Not at vendor'))"></dd>
                        </div>
                        <div class="production-floor-panel-status-card">
                            <dt>{{ __('Priority') }}</dt>
                            <dd class="capitalize" x-text="panel.job?.priority_label ?? '—'"></dd>
                        </div>
                    </div>

                    <div class="rounded-lg border border-erp-border bg-white p-3">
                        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-erp-primary">{{ __('Next required action') }}</h3>
                        <div class="flex flex-wrap gap-2">
                            <template x-if="panel.primary_action?.type === 'post'">
                                <form :action="panel.primary_action.url" method="POST" @if ($operatorMode) data-erp-desk-form @endif>
                                    <input type="hidden" name="_token" :value="csrf">
                                    @if ($operatorMode)
                                        <input type="hidden" name="from" value="production-floor">
                                    @endif
                                    <button type="submit" class="erp-btn-primary text-sm" x-text="panel.primary_action.label"></button>
                                </form>
                            </template>
                            <template x-if="panel.primary_action?.type === 'modal' || panel.primary_action?.type === 'qc'">
                                <button type="button" class="erp-btn-primary text-sm" x-text="panel.primary_action.label" @click="openActionModal(panel.job.public_id, panel.primary_action.target ?? 'qc')"></button>
                            </template>
                            <template x-if="panel.primary_action?.type === 'panel'">
                                <button type="button" class="erp-btn-primary text-sm" x-text="panel.primary_action.label" @click="openActionModal(panel.job.public_id, panel.primary_action.target ?? 'machine')"></button>
                            </template>
                            <template x-if="panel.primary_action?.type === 'link'">
                                <a :href="panel.primary_action.url" class="erp-btn-primary text-sm" @if ($operatorMode) data-erp-modal-open @else data-turbo-frame="erp-main" @endif x-text="panel.primary_action.label"></a>
                            </template>
                            <template x-if="!panel.primary_action">
                                <p class="text-sm text-slate-500">{{ __('No immediate action — review job details below.') }}</p>
                            </template>
                            <template x-if="panel.header?.job_sheet_url">
                                <a :href="panel.header.job_sheet_url" target="_blank" rel="noopener" class="erp-btn-secondary text-sm">{{ __('Print job sheet') }}</a>
                            </template>
                            <template x-if="panel.header?.label_url">
                                <a :href="panel.header.label_url" target="_blank" rel="noopener" class="erp-btn-secondary text-sm">{{ __('Print label') }}</a>
                            </template>
                            @if ($operatorMode)
                                <template x-if="panel.links?.job">
                                    <a :href="panel.links.job" class="erp-btn-secondary text-sm" data-erp-modal-open>{{ __('Preview job') }}</a>
                                </template>
                                <template x-if="panel.links?.sales_order">
                                    <a :href="panel.links.sales_order" class="erp-btn-secondary text-sm" data-erp-modal-open x-text="panel.header?.sales_order_number ? ('{{ __('Sales order') }} · ' + panel.header.sales_order_number) : '{{ __('Sales order') }}'"></a>
                                </template>
                            @else
                                <template x-if="panel.links?.job">
                                    <a :href="panel.links.job" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ __('Full job workspace') }}</a>
                                </template>
                                <template x-if="panel.links?.sales_order">
                                    <a :href="panel.links.sales_order" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main" x-text="panel.header?.sales_order_number ? ('{{ __('Sales order') }} · ' + panel.header.sales_order_number) : '{{ __('Sales order') }}'"></a>
                                </template>
                            @endif
                        </div>
                    </div>

                    <template x-if="panel.blockers?.length">
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                            <p class="mb-1 font-medium">{{ __('Before dispatch') }}</p>
                            <ul class="list-disc space-y-0.5 pl-4">
                                <template x-for="blocker in panel.blockers" :key="blocker">
                                    <li x-text="blocker"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <section id="outsource" class="rounded-lg border border-violet-200 bg-violet-50/40 p-4">
                        <h3 class="mb-2 text-sm font-semibold text-violet-900">{{ __('At vendor') }}</h3>
                        <template x-if="panel.outsource?.vendor">
                            <dl class="mb-3 grid grid-cols-2 gap-2 text-xs">
                                <div><dt class="text-slate-500">{{ __('Vendor') }}</dt><dd class="font-medium" x-text="panel.outsource.vendor.vendor_name"></dd></div>
                                <div><dt class="text-slate-500">{{ __('Expected return') }}</dt><dd x-text="panel.outsource.expected_return ?? '—'"></dd></div>
                                <div><dt class="text-slate-500">{{ __('Quoted') }}</dt><dd x-text="panel.outsource.quoted_cost ?? '—'"></dd></div>
                                <div><dt class="text-slate-500">{{ __('Actual') }}</dt><dd x-text="panel.outsource.actual_cost ?? '—'"></dd></div>
                            </dl>
                        </template>
                        <template x-if="panel.outsource?.can_outsource">
                            <form :action="panel.outsource.outsource_url" method="POST" class="grid grid-cols-1 gap-2 text-sm" @if ($operatorMode) data-erp-desk-form @endif>
                                <input type="hidden" name="_token" :value="csrf">
                                @if ($operatorMode)
                                    <input type="hidden" name="from" value="production-floor">
                                @endif
                                <div>
                                    <label class="erp-label text-xs">{{ __('Production vendor') }}</label>
                                    <select name="outsource_vendor_id" class="erp-select w-full" required>
                                        <template x-for="vendor in panel.outsource.production_vendors" :key="vendor.id">
                                            <option :value="vendor.id" x-text="vendor.vendor_name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="erp-label text-xs">{{ __('Issue date') }}</label>
                                        <input type="date" name="outsource_issue_date" class="erp-input w-full" required :value="new Date().toISOString().slice(0, 10)">
                                    </div>
                                    <div>
                                        <label class="erp-label text-xs">{{ __('Expected return') }}</label>
                                        <input type="date" name="outsource_expected_return" class="erp-input w-full">
                                    </div>
                                </div>
                                <div>
                                    <label class="erp-label text-xs">{{ __('Quoted cost') }}</label>
                                    <input type="number" step="0.01" name="outsource_quoted_cost" class="erp-input w-full">
                                </div>
                                <div>
                                    <label class="erp-label text-xs">{{ __('Notes') }}</label>
                                    <textarea name="outsource_notes" class="erp-input w-full" rows="2"></textarea>
                                </div>
                                <button type="submit" class="erp-btn-primary text-sm">{{ __('Send to vendor') }}</button>
                            </form>
                        </template>
                        <template x-if="panel.outsource?.can_return">
                            <form :action="panel.outsource.return_url" method="POST" class="flex flex-wrap items-end gap-2" @if ($operatorMode) data-erp-desk-form @endif>
                                <input type="hidden" name="_token" :value="csrf">
                                @if ($operatorMode)
                                    <input type="hidden" name="from" value="production-floor">
                                @endif
                                <div class="min-w-[10rem] flex-1">
                                    <label class="erp-label text-xs">{{ __('Actual cost') }}</label>
                                    <input type="number" step="0.01" name="outsource_actual_cost" class="erp-input w-full">
                                </div>
                                <button type="submit" class="erp-btn-primary text-sm">{{ __('Mark returned') }}</button>
                            </form>
                        </template>
                        <template x-if="!panel.outsource?.vendor && !panel.outsource?.can_outsource && !panel.outsource?.can_return">
                            <p class="text-xs text-slate-500">{{ __('Not currently at a vendor.') }}</p>
                        </template>
                    </section>

                    <section id="fulfilment" class="rounded-lg border border-erp-border p-4">
                        <h3 class="mb-2 text-sm font-semibold text-erp-primary">{{ __('Fulfilment') }}</h3>
                        <p class="mb-2 text-xs text-slate-600">
                            {{ __('Status') }}:
                            <span x-text="panel.fulfilment?.status_label ?? '—'"></span>
                        </p>
                        @unless ($operatorMode)
                        <a
                            x-show="panel.links?.job"
                            :href="panel.links.job + '?tab=fulfilment'"
                            class="text-sm text-erp-accent hover:underline"
                            data-turbo-frame="erp-main"
                        >{{ __('Open fulfilment tab') }}</a>
                        @endunless
                    </section>

                    <section id="quality" class="rounded-lg border border-erp-border p-4" x-data="{ qcDecision: 'passed' }">
                        <h3 class="mb-2 text-sm font-semibold text-erp-primary">{{ __('Quality & handoff') }}</h3>
                        <p class="mb-3 text-xs text-slate-600">
                            {{ __('Fulfilment') }}:
                            <span x-text="panel.fulfilment?.status_label ?? '—'"></span>
                        </p>

                        <template x-if="panel.quality?.can_record">
                            <div class="mb-4 rounded-lg border border-purple-200 bg-purple-50/40 p-3">
                                <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-purple-900">{{ __('Record inspection') }}</h4>
                                <form
                                    :action="panel.quality.store_url"
                                    method="POST"
                                    class="space-y-3"
                                    @if ($operatorMode) data-erp-desk-form @endif
                                >
                                    <input type="hidden" name="_token" :value="csrf">
                                    @if ($operatorMode)
                                        <input type="hidden" name="from" value="production-floor">
                                    @endif
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        <div>
                                            <label class="erp-label text-xs">{{ __('Inspection date') }}</label>
                                            <input type="date" name="inspection_date" class="erp-input w-full text-sm" :value="new Date().toISOString().slice(0, 10)">
                                        </div>
                                        <div>
                                            <label class="erp-label text-xs">{{ __('Decision') }}</label>
                                            <select name="result" class="erp-select w-full text-sm" required x-model="qcDecision">
                                                <option value="passed">{{ __('Pass') }}</option>
                                                <option value="failed">{{ __('Fail') }}</option>
                                                <option value="conditional_pass">{{ __('Conditional pass') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div x-show="qcDecision === 'conditional_pass'" x-cloak>
                                        <label class="inline-flex items-center gap-2 text-xs">
                                            <input type="checkbox" name="requires_customer_approval" value="1">
                                            {{ __('Requires customer approval') }}
                                        </label>
                                    </div>
                                    @include('admin.production.floor.partials.qc-checklist-table', [
                                        'itemsExpression' => 'panel.quality.checklist_items',
                                    ])
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2" x-show="qcDecision === 'failed' || qcDecision === 'conditional_pass'" x-cloak>
                                        <div>
                                            <label class="erp-label text-xs">{{ __('Fail reason') }}</label>
                                            <select name="fail_reason" class="erp-select w-full text-sm">
                                                <option value="">{{ __('—') }}</option>
                                                <template x-for="reason in panel.quality.fail_reasons ?? []" :key="reason.value">
                                                    <option :value="reason.value" x-text="reason.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="erp-label text-xs">{{ __('Rework reason') }}</label>
                                            <select name="rework_reason" class="erp-select w-full text-sm">
                                                <option value="">{{ __('—') }}</option>
                                                <template x-for="reason in panel.quality.rework_reasons ?? []" :key="reason.value">
                                                    <option :value="reason.value" x-text="reason.label"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="erp-label text-xs">{{ __('Notes') }}</label>
                                        <textarea name="comments" class="erp-input w-full text-sm" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Save inspection') }}</button>
                                </form>
                                <template x-if="panel.quality?.can_quick_pass">
                                    <form :action="panel.quality.quick_pass_url" method="POST" class="mt-2" @if ($operatorMode) data-erp-desk-form @endif>
                                        <input type="hidden" name="_token" :value="csrf">
                                        @if ($operatorMode)
                                            <input type="hidden" name="from" value="production-floor">
                                        @endif
                                        <button type="submit" class="erp-btn-secondary text-sm">{{ __('Quick pass') }}</button>
                                    </form>
                                </template>
                            </div>
                        </template>

                        <template x-if="panel.quality?.can_record === false">
                            <p class="mb-3 text-xs text-slate-500">{{ __('This job is not awaiting a new inspection.') }}</p>
                        </template>

                        @unless ($operatorMode)
                        <div class="flex flex-wrap gap-3 text-sm">
                            <a
                                x-show="panel.links?.job"
                                :href="panel.links.job + '?tab=quality'"
                                class="text-erp-accent hover:underline"
                                data-turbo-frame="erp-main"
                            >{{ __('Quality tab') }}</a>
                            <a
                                x-show="panel.links?.job"
                                :href="panel.links.job + '?tab=fulfilment'"
                                class="text-erp-accent hover:underline"
                                data-turbo-frame="erp-main"
                            >{{ __('Fulfilment') }}</a>
                        </div>
                        @endunless
                    </section>
                </div>
            </template>
        </div>

        <div class="flex flex-1 items-center justify-center p-8" x-show="panelLoading">
            <p class="text-sm text-slate-500">{{ __('Loading job…') }}</p>
        </div>
    </aside>
</div>
