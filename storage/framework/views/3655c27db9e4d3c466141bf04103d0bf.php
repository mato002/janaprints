
<div x-show="showCloseDrawer" x-cloak>
    <div class="fixed inset-0 z-40 bg-erp-primary/40" @click="showCloseDrawer = false"></div>
    <div class="fixed inset-y-0 right-0 z-50 w-full max-w-md overflow-y-auto border-l border-erp-border bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold"><?php echo e(__('Close session')); ?></h3>
        <template x-if="closePreview">
            <div class="mt-4 space-y-4">
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div class="rounded border border-erp-border p-2"><span class="text-xs text-slate-500"><?php echo e(__('Expected cash')); ?></span><p class="font-medium tabular-nums" x-text="formatMoney(closePreview.expected_cash)"></p></div>
                    <div class="rounded border border-erp-border p-2"><span class="text-xs text-slate-500"><?php echo e(__('Expected M-Pesa')); ?></span><p class="font-medium tabular-nums" x-text="formatMoney(closePreview.expected_mpesa)"></p></div>
                    <div class="rounded border border-erp-border p-2"><span class="text-xs text-slate-500"><?php echo e(__('Expected card')); ?></span><p class="font-medium tabular-nums" x-text="formatMoney(closePreview.expected_card)"></p></div>
                    <div class="rounded border border-erp-border p-2"><span class="text-xs text-slate-500"><?php echo e(__('Expected bank')); ?></span><p class="font-medium tabular-nums" x-text="formatMoney(closePreview.expected_bank)"></p></div>
                    <div class="col-span-2 rounded border border-erp-border p-2"><span class="text-xs text-slate-500"><?php echo e(__('Expected total')); ?></span><p class="font-medium tabular-nums" x-text="formatMoney(closePreview.expected_total)"></p></div>
                </div>
                <div x-show="!closePreview.can_close" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900"><?php echo e(__('Resolve held sales and pending items before closing.')); ?></div>
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Actual cash count')); ?></label>
                    <input type="number" step="0.01" min="0" class="erp-input mt-1 w-full" x-model.number="closeForm.actual_cash" :disabled="!closePreview.can_close">
                </div>
                <div class="rounded border border-erp-border bg-slate-50 px-3 py-2 text-sm">
                    <div class="flex justify-between"><span><?php echo e(__('Variance')); ?></span><span class="font-semibold tabular-nums" x-text="formatMoney(closeVariance)"></span></div>
                    <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Tolerance')); ?>: <span x-text="formatMoney(closePreview.variance_tolerance)"></span></p>
                    <p class="mt-1 text-xs text-amber-700" x-show="Math.abs(closeVariance) > closePreview.variance_tolerance"><?php echo e(__('Manager approval will be required.')); ?></p>
                </div>
                <div>
                    <label class="text-xs text-slate-500"><?php echo e(__('Closing notes')); ?></label>
                    <textarea class="erp-input mt-1 w-full" rows="2" x-model="closeForm.closing_notes"></textarea>
                </div>
                <p class="text-sm text-red-600" x-text="closeError" x-show="closeError"></p>
                <div class="flex gap-2">
                    <button type="button" class="erp-btn-primary" @click="submitCloseSession()" :disabled="loading || !closePreview.can_close"><?php echo e(__('Close session')); ?></button>
                    <button type="button" class="erp-btn-secondary" @click="showCloseDrawer = false"><?php echo e(__('Cancel')); ?></button>
                </div>
            </div>
        </template>
        <p class="mt-4 text-sm text-slate-500" x-show="!closePreview && !loading"><?php echo e(__('Loading…')); ?></p>
    </div>
</div>


<div x-show="showHeldDrawer" x-cloak>
    <div class="fixed inset-0 z-40 bg-erp-primary/40" @click="showHeldDrawer = false"></div>
    <div class="fixed inset-y-0 right-0 z-50 w-full max-w-lg overflow-y-auto border-l border-erp-border bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold"><?php echo e(__('Held sales queue')); ?></h3>
        <div class="mt-4 space-y-2" x-show="heldSales.length">
            <template x-for="hold in heldSales" :key="hold.id">
                <div class="rounded-lg border border-erp-border p-3 text-sm">
                    <div class="flex justify-between gap-2">
                        <span class="font-mono font-medium" x-text="hold.sale_number"></span>
                        <span class="tabular-nums font-medium" x-text="formatMoney(hold.value)"></span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        <span x-text="hold.customer"></span> · <span x-text="hold.cashier"></span> · <span x-text="hold.held_at"></span>
                    </p>
                    <div class="mt-2 flex gap-2">
                        <button type="button" class="erp-btn-primary text-xs" @click="resumeHeld(hold)"><?php echo e(__('Resume')); ?></button>
                        <button type="button" class="erp-btn-secondary text-xs text-red-700" @click="cancelHeld(hold)" x-show="permissions.canCancel"><?php echo e(__('Cancel')); ?></button>
                    </div>
                </div>
            </template>
        </div>
        <p class="mt-4 text-sm text-slate-500" x-show="!heldSales.length && !heldLoading"><?php echo e(__('No held sales in queue.')); ?></p>
        <p class="mt-4 text-sm text-slate-500" x-show="heldLoading"><?php echo e(__('Loading…')); ?></p>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/commercial/pos/partials/workstation/drawers.blade.php ENDPATH**/ ?>