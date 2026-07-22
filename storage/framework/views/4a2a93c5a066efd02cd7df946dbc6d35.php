<?php
    $operatorMode = (bool) ($operatorMode ?? false);
?>

<div
    x-show="qcModalOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    @keydown.escape.window="closeQcModal()"
>
    <div class="absolute inset-0 bg-slate-900/40" @click="closeQcModal()"></div>
    <div
        class="relative z-10 flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-lg border border-erp-border bg-white shadow-xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="production-floor-qc-modal-title"
        @click.stop
    >
        <div class="flex items-start justify-between border-b border-erp-border px-4 py-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500"><?php echo e(__('Record inspection')); ?></p>
                <h3 id="production-floor-qc-modal-title" class="text-lg font-semibold text-erp-primary" x-text="qcModalPanel?.header?.job_number ?? '—'"></h3>
                <p class="text-sm text-slate-600">
                    <span x-text="qcModalPanel?.header?.customer ?? '—'"></span>
                    ·
                    <span x-text="qcModalPanel?.header?.product ?? '—'"></span>
                </p>
            </div>
            <button type="button" class="erp-btn-secondary text-sm" @click="closeQcModal()"><?php echo e(__('Close')); ?></button>
        </div>

        <div class="flex-1 overflow-y-auto p-4" x-show="qcModalLoading">
            <p class="text-sm text-slate-500"><?php echo e(__('Loading inspection form…')); ?></p>
        </div>

        <div class="flex-1 overflow-y-auto p-4" x-show="!qcModalLoading && qcModalPanel" x-data="{ qcDecision: 'passed' }">
            <template x-if="qcModalPanel?.quality?.can_record">
                <form
                    :action="qcModalPanel.quality.store_url"
                    method="POST"
                    class="space-y-3"
                    <?php if($operatorMode): ?> data-erp-desk-form <?php endif; ?>
                >
                    <input type="hidden" name="_token" :value="csrf">
                    <?php if($operatorMode): ?>
                        <input type="hidden" name="from" value="production-floor">
                    <?php endif; ?>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div>
                            <label class="erp-label text-xs"><?php echo e(__('Inspection date')); ?></label>
                            <input type="date" name="inspection_date" class="erp-input w-full text-sm" :value="new Date().toISOString().slice(0, 10)">
                        </div>
                        <div>
                            <label class="erp-label text-xs"><?php echo e(__('Decision')); ?></label>
                            <select name="result" class="erp-select w-full text-sm" required x-model="qcDecision">
                                <option value="passed"><?php echo e(__('Pass')); ?></option>
                                <option value="failed"><?php echo e(__('Fail')); ?></option>
                                <option value="conditional_pass"><?php echo e(__('Conditional pass')); ?></option>
                            </select>
                        </div>
                    </div>
                    <div x-show="qcDecision === 'conditional_pass'" x-cloak>
                        <label class="inline-flex items-center gap-2 text-xs">
                            <input type="checkbox" name="requires_customer_approval" value="1">
                            <?php echo e(__('Requires customer approval')); ?>

                        </label>
                    </div>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2" x-show="qcDecision === 'failed' || qcDecision === 'conditional_pass'" x-cloak>
                        <div>
                            <label class="erp-label text-xs"><?php echo e(__('Fail reason')); ?></label>
                            <select name="fail_reason" class="erp-select w-full text-sm">
                                <option value=""><?php echo e(__('—')); ?></option>
                                <template x-for="reason in qcModalPanel.quality.fail_reasons ?? []" :key="reason.value">
                                    <option :value="reason.value" x-text="reason.label"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="erp-label text-xs"><?php echo e(__('Rework reason')); ?></label>
                            <select name="rework_reason" class="erp-select w-full text-sm">
                                <option value=""><?php echo e(__('—')); ?></option>
                                <template x-for="reason in qcModalPanel.quality.rework_reasons ?? []" :key="reason.value">
                                    <option :value="reason.value" x-text="reason.label"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="erp-label text-xs"><?php echo e(__('Notes')); ?></label>
                        <textarea name="comments" class="erp-input w-full text-sm" rows="3" placeholder="<?php echo e(__('Inspection notes…')); ?>"></textarea>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-erp-border pt-3">
                        <button type="button" class="erp-btn-secondary text-sm" @click="closeQcModal()"><?php echo e(__('Cancel')); ?></button>
                        <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Save inspection')); ?></button>
                    </div>
                </form>
            </template>
            <template x-if="qcModalPanel && !qcModalPanel?.quality?.can_record">
                <p class="text-sm text-slate-500"><?php echo e(__('This job is not awaiting a new inspection.')); ?></p>
            </template>
            <template x-if="qcModalPanel?.quality?.can_quick_pass">
                <form :action="qcModalPanel.quality.quick_pass_url" method="POST" class="mt-3 border-t border-erp-border pt-3" <?php if($operatorMode): ?> data-erp-desk-form <?php endif; ?>>
                    <input type="hidden" name="_token" :value="csrf">
                    <?php if($operatorMode): ?>
                        <input type="hidden" name="from" value="production-floor">
                    <?php endif; ?>
                    <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Quick pass')); ?></button>
                </form>
            </template>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/floor/partials/qc-modal.blade.php ENDPATH**/ ?>