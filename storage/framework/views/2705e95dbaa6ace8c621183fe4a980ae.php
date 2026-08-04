<div class="mb-4 rounded-lg border px-4 py-3" :class="hasSession ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50'">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold" :class="hasSession ? 'text-emerald-900' : 'text-rose-900'">
                <span x-show="hasSession"><?php echo e(__('Active session')); ?></span>
                <span x-show="!hasSession"><?php echo e(__('No active session')); ?></span>
            </h3>
            <template x-if="hasSession">
                <p class="mt-1 text-sm text-emerald-800">
                    <span x-text="sessionData.session_number"></span>
                    · <span x-text="sessionData.cashier_name"></span>
                    · <span x-text="sessionData.opened_at"></span>
                </p>
            </template>
            <template x-if="!hasSession">
                <p class="mt-1 text-sm text-rose-800"><?php echo e(__('Open a POS session before processing sales.')); ?></p>
            </template>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="erp-btn-primary text-sm" @click="showOpenSessionModal = true" x-show="!hasSession && permissions.canOpenSession"><?php echo e(__('Open session')); ?></button>
            <button type="button" class="erp-btn-secondary text-sm" @click="openCloseDrawer()" x-show="hasSession && permissions.canCloseSession"><?php echo e(__('Close session')); ?></button>
        </div>
    </div>
    <div class="mt-3 grid grid-cols-2 gap-2 text-sm lg:grid-cols-4" x-show="hasSession">
        <div><span class="text-xs text-emerald-700"><?php echo e(__('Opening float')); ?></span><p class="font-medium tabular-nums" x-text="formatMoney(sessionData.opening_float)"></p></div>
        <div><span class="text-xs text-emerald-700"><?php echo e(__('Sales count')); ?></span><p class="font-medium tabular-nums" x-text="sessionMetrics.sales_count ?? 0"></p></div>
        <div><span class="text-xs text-emerald-700"><?php echo e(__('Sales total')); ?></span><p class="font-medium tabular-nums" x-text="formatMoney(sessionMetrics.total_sales_value ?? 0)"></p></div>
        <div><span class="text-xs text-emerald-700"><?php echo e(__('Terminal')); ?></span><p class="font-medium" x-text="sessionData.terminal || '—'"></p></div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\pos\partials\workstation\session-widget.blade.php ENDPATH**/ ?>