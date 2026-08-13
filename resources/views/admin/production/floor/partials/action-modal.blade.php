@php
    $operatorMode = (bool) ($operatorMode ?? false);
@endphp

<div
    x-show="actionModalOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="closeActionModal()"
>
    <div class="absolute inset-0 bg-slate-900/40" @click="closeActionModal()"></div>
    <div
        class="relative z-10 flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-lg border border-erp-border bg-white shadow-xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="production-floor-action-modal-title"
        @click.stop
    >
        <div class="flex items-start justify-between border-b border-erp-border px-4 py-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500" x-text="actionModalSubtitle()"></p>
                <h3 id="production-floor-action-modal-title" class="text-lg font-semibold text-erp-primary" x-text="actionModalPanel?.header?.job_number ?? '—'"></h3>
                <p class="text-sm text-slate-600">
                    <span x-text="actionModalPanel?.header?.customer ?? '—'"></span>
                    <span x-show="actionModalPanel?.header?.product"> · </span>
                    <span x-text="actionModalPanel?.header?.product ?? ''"></span>
                </p>
            </div>
            <button type="button" class="erp-btn-secondary text-sm" @click="closeActionModal()">{{ __('Close') }}</button>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            <p class="text-sm text-slate-500" x-show="actionModalLoading" x-cloak>{{ __('Loading…') }}</p>

            {{-- Assign operator --}}
            <div x-show="!actionModalLoading && actionModalTarget === 'operator'" x-cloak>
                <form
                    method="POST"
                    :action="`${assignMachineUrl}/${actionModalPanel?.job?.public_id}/assign-operator`"
                    class="space-y-3"
                    @if ($operatorMode) data-erp-desk-form @endif
                >
                    <input type="hidden" name="_token" :value="csrf">
                    <input type="hidden" name="production_queue_id" :value="actionModalPanel?.execution?.queue_id ?? ''">
                    @if ($operatorMode)
                        <input type="hidden" name="from" value="production-floor">
                    @endif
                    <div>
                        <label class="erp-label text-xs">{{ __('Operator') }}</label>
                        <div class="erp-lookup-select__row mt-1 flex items-stretch gap-2">
                            <select
                                name="assigned_operator_id"
                                class="erp-select erp-lookup-select__input min-w-0 flex-1 text-sm"
                                x-model="selectedOperatorId"
                                x-ref="operatorSelect"
                                required
                            >
                                <option value="">{{ __('Select operator') }}</option>
                                <template x-for="operator in actionModalPanel?.operators ?? []" :key="operator.id">
                                    <option :value="String(operator.id)" x-text="operator.name"></option>
                                </template>
                            </select>
                            @can('employees.manage')
                                <button
                                    type="button"
                                    class="erp-lookup-select__add"
                                    @click.stop.prevent="openCreateOperator()"
                                    title="{{ __('Add new operator') }}"
                                    aria-label="{{ __('Add new operator') }}"
                                >
                                    <span aria-hidden="true">+</span>
                                </button>
                            @endcan
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">{{ __('Optional. Assigning an operator marks this queue entry Ready.') }}</p>
                    <div class="flex justify-end gap-2 border-t border-erp-border pt-3">
                        <button type="button" class="erp-btn-secondary text-sm" @click="closeActionModal()">{{ __('Cancel') }}</button>
                        <button type="submit" class="erp-btn-primary text-sm">{{ __('Assign operator') }}</button>
                    </div>
                </form>
            </div>

            {{-- Assign machine --}}
            <div x-show="!actionModalLoading && actionModalTarget === 'machine'" x-cloak>
                <form
                    method="POST"
                    :action="`${assignMachineUrl}/${actionModalPanel?.job?.public_id}/assign-machine`"
                    class="space-y-3"
                    @submit.prevent="submitActionModalAssignMachine($event)"
                >
                    <input type="hidden" name="_token" :value="csrf">
                    @if ($operatorMode)
                        <input type="hidden" name="from" value="production-floor">
                    @endif
                    <div>
                        <label class="erp-label text-xs">{{ __('Machine') }}</label>
                        <select name="assigned_machine_asset_id" class="erp-select w-full text-sm" x-model="actionModalMachineId" required>
                            <option value="">{{ __('Assign') }}</option>
                            <template x-for="machine in machines" :key="machine.value">
                                <option :value="machine.value" x-text="machine.label"></option>
                            </template>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 border-t border-erp-border pt-3">
                        <button type="button" class="erp-btn-secondary text-sm" @click="closeActionModal()">{{ __('Cancel') }}</button>
                        <button type="submit" class="erp-btn-primary text-sm" :disabled="actionModalAssignSubmitting">
                            <span x-show="!actionModalAssignSubmitting">{{ __('Assign machine') }}</span>
                            <span x-show="actionModalAssignSubmitting">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Send to vendor --}}
            <div x-show="!actionModalLoading && actionModalTarget === 'outsource-send'" x-cloak>
                <p class="mb-3 text-sm text-slate-500" x-show="!actionModalPanel?.outsource?.can_outsource" x-cloak>{{ __('This job cannot be sent to a vendor right now.') }}</p>
                <form
                    x-show="actionModalPanel?.outsource?.can_outsource"
                    :action="actionModalPanel?.outsource?.outsource_url"
                    method="POST"
                    class="grid grid-cols-1 gap-2 text-sm"
                    @if ($operatorMode) data-erp-desk-form @endif
                >
                    <input type="hidden" name="_token" :value="csrf">
                    @if ($operatorMode)
                        <input type="hidden" name="from" value="production-floor">
                    @endif
                    <div>
                        <label class="erp-label text-xs">{{ __('Production vendor') }}</label>
                        <select name="outsource_vendor_id" class="erp-select w-full" required>
                            <template x-for="vendor in actionModalPanel?.outsource?.production_vendors ?? []" :key="vendor.id">
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
                    <div class="flex justify-end gap-2 border-t border-erp-border pt-3">
                        <button type="button" class="erp-btn-secondary text-sm" @click="closeActionModal()">{{ __('Cancel') }}</button>
                        <button type="submit" class="erp-btn-primary text-sm">{{ __('Send to vendor') }}</button>
                    </div>
                </form>
            </div>

            {{-- Mark returned from vendor --}}
            <div x-show="!actionModalLoading && actionModalTarget === 'outsource-return'" x-cloak>
                <dl class="mb-3 grid grid-cols-2 gap-2 text-xs" x-show="actionModalPanel?.outsource?.vendor">
                    <div><dt class="text-slate-500">{{ __('Vendor') }}</dt><dd class="font-medium" x-text="actionModalPanel.outsource.vendor.vendor_name"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Expected return') }}</dt><dd x-text="actionModalPanel?.outsource?.expected_return ?? '—'"></dd></div>
                </dl>
                <p class="mb-3 text-sm text-slate-500" x-show="!actionModalPanel?.outsource?.can_return" x-cloak>{{ __('This job is not awaiting a vendor return.') }}</p>
                <form
                    x-show="actionModalPanel?.outsource?.can_return"
                    :action="actionModalPanel?.outsource?.return_url"
                    method="POST"
                    class="space-y-3"
                    @if ($operatorMode) data-erp-desk-form @endif
                >
                    <input type="hidden" name="_token" :value="csrf">
                    @if ($operatorMode)
                        <input type="hidden" name="from" value="production-floor">
                    @endif
                    <div>
                        <label class="erp-label text-xs">{{ __('Actual cost') }}</label>
                        <input type="number" step="0.01" name="outsource_actual_cost" class="erp-input w-full">
                    </div>
                    <div class="flex justify-end gap-2 border-t border-erp-border pt-3">
                        <button type="button" class="erp-btn-secondary text-sm" @click="closeActionModal()">{{ __('Cancel') }}</button>
                        <button type="submit" class="erp-btn-primary text-sm">{{ __('Mark returned') }}</button>
                    </div>
                </form>
            </div>

            {{-- QC --}}
            <div x-show="!actionModalLoading && actionModalTarget === 'qc'" x-cloak>
                <p
                    class="text-sm text-slate-500"
                    x-show="actionModalPanel && !actionModalPanel.quality?.can_record"
                >{{ __('This job is not awaiting a new inspection.') }}</p>
                <form
                    x-show="actionModalPanel?.quality?.can_record"
                    :action="actionModalPanel?.quality?.store_url"
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
                        'itemsExpression' => 'actionModalPanel?.quality?.checklist_items',
                    ])
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2" x-show="qcDecision === 'failed' || qcDecision === 'conditional_pass'" x-cloak>
                        <div>
                            <label class="erp-label text-xs">{{ __('Fail reason') }}</label>
                            <select name="fail_reason" class="erp-select w-full text-sm">
                                <option value="">{{ __('—') }}</option>
                                <template x-for="reason in actionModalPanel?.quality?.fail_reasons ?? []" :key="reason.value">
                                    <option :value="reason.value" x-text="reason.label"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="erp-label text-xs">{{ __('Rework reason') }}</label>
                            <select name="rework_reason" class="erp-select w-full text-sm">
                                <option value="">{{ __('—') }}</option>
                                <template x-for="reason in actionModalPanel?.quality?.rework_reasons ?? []" :key="reason.value">
                                    <option :value="reason.value" x-text="reason.label"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="erp-label text-xs">{{ __('Notes') }}</label>
                        <textarea name="comments" class="erp-input w-full text-sm" rows="3" placeholder="{{ __('Inspection notes…') }}"></textarea>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-erp-border pt-3">
                        <button type="button" class="erp-btn-secondary text-sm" @click="closeActionModal()">{{ __('Cancel') }}</button>
                        <button type="submit" class="erp-btn-primary text-sm">{{ __('Save inspection') }}</button>
                    </div>
                </form>
                <form
                    x-show="actionModalPanel?.quality?.can_quick_pass"
                    :action="actionModalPanel?.quality?.quick_pass_url"
                    method="POST"
                    class="mt-3 border-t border-erp-border pt-3"
                    @if ($operatorMode) data-erp-desk-form @endif
                >
                    <input type="hidden" name="_token" :value="csrf">
                    @if ($operatorMode)
                        <input type="hidden" name="from" value="production-floor">
                    @endif
                    <button type="submit" class="erp-btn-secondary text-sm">{{ __('Quick pass') }}</button>
                </form>
            </div>

            {{-- Hand off / fulfilment --}}
            <div x-show="!actionModalLoading && actionModalTarget === 'fulfilment'" x-cloak class="space-y-3">
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-slate-500">{{ __('Fulfilment status') }}</dt><dd class="font-medium" x-text="actionModalPanel?.fulfilment?.status_label ?? '—'"></dd></div>
                    <div><dt class="text-slate-500">{{ __('Required date') }}</dt><dd x-text="actionModalPanel?.header?.required_date ?? '—'"></dd></div>
                </dl>
                <div x-show="(actionModalPanel?.blockers ?? []).length" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                    <p class="mb-1 font-medium">{{ __('Before dispatch') }}</p>
                    <ul class="list-disc space-y-0.5 pl-4">
                        <template x-for="blocker in actionModalPanel?.blockers ?? []" :key="blocker">
                            <li x-text="blocker"></li>
                        </template>
                    </ul>
                </div>
                <div class="flex flex-wrap gap-2 border-t border-erp-border pt-3">
                    <a
                        x-show="actionModalPanel?.header?.label_url"
                        :href="actionModalPanel?.header?.label_url"
                        target="_blank"
                        rel="noopener"
                        class="erp-btn-secondary text-sm"
                    >{{ __('Print label') }}</a>
                    @if ($operatorMode)
                        <a
                            x-show="actionModalPanel?.links?.job"
                            :href="actionModalPanel?.links?.job"
                            class="erp-btn-secondary text-sm"
                            data-erp-modal-open
                        >{{ __('Preview job') }}</a>
                    @else
                        <a
                            x-show="actionModalPanel?.links?.job"
                            :href="(actionModalPanel?.links?.job ?? '') + '?tab=fulfilment'"
                            class="erp-btn-secondary text-sm"
                            data-turbo-frame="erp-main"
                        >{{ __('Open fulfilment') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
