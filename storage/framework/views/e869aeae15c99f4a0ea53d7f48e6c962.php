<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mx-auto max-w-2xl print:shadow-none','id' => 'pos-session-summary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto max-w-2xl print:shadow-none','id' => 'pos-session-summary']); ?>
    <div class="text-center border-b border-erp-border pb-4 mb-4">
        <h2 class="text-lg font-semibold"><?php echo e(__('Session summary')); ?></h2>
        <p class="font-mono text-sm mt-1"><?php echo e($session->session_number); ?></p>
        <p class="text-xs text-slate-500"><?php echo e($session->branch?->name); ?></p>
    </div>

    <dl class="grid grid-cols-2 gap-3 text-sm mb-6">
        <div><dt class="text-slate-500"><?php echo e(__('Cashier')); ?></dt><dd class="font-medium"><?php echo e($session->cashier?->name); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Terminal')); ?></dt><dd class="font-medium"><?php echo e($session->terminal ?? '—'); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Opened')); ?></dt><dd><?php echo e($session->opened_at?->format('Y-m-d H:i')); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Closed')); ?></dt><dd><?php echo e($session->closed_at?->format('Y-m-d H:i') ?? '—'); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Opening float')); ?></dt><dd class="tabular-nums"><?php echo e(number_format($session->opening_float, 2)); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Status')); ?></dt><dd class="font-medium"><?php echo e(ucfirst(str_replace('_', ' ', $session->status->value))); ?></dd></div>
    </dl>

    <h3 class="text-sm font-semibold mb-2"><?php echo e(__('Sales summary')); ?></h3>
    <div class="grid grid-cols-2 gap-2 text-sm mb-6">
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span><?php echo e(__('Transactions')); ?></span><span class="tabular-nums"><?php echo e($metrics['transactions_count']); ?></span></div>
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span><?php echo e(__('Paid sales')); ?></span><span class="tabular-nums"><?php echo e($metrics['sales_count']); ?></span></div>
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span><?php echo e(__('Held sales')); ?></span><span class="tabular-nums"><?php echo e($metrics['held_sales']); ?></span></div>
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span><?php echo e(__('Cancelled sales')); ?></span><span class="tabular-nums"><?php echo e($metrics['cancelled_sales']); ?></span></div>
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span><?php echo e(__('Total sales value')); ?></span><span class="tabular-nums font-medium"><?php echo e(number_format($metrics['total_sales_value'], 2)); ?></span></div>
        <div class="flex justify-between border-b border-erp-border/60 py-1"><span><?php echo e(__('Refunds')); ?></span><span class="tabular-nums"><?php echo e($metrics['refunds']); ?></span></div>
    </div>

    <h3 class="text-sm font-semibold mb-2"><?php echo e(__('Payment summary')); ?></h3>
    <div class="space-y-1 text-sm mb-6">
        <div class="flex justify-between"><span><?php echo e(__('Cash')); ?></span><span class="tabular-nums"><?php echo e(number_format($metrics['cash_sales'], 2)); ?></span></div>
        <div class="flex justify-between"><span><?php echo e(__('M-Pesa')); ?></span><span class="tabular-nums"><?php echo e(number_format($metrics['mpesa_sales'], 2)); ?></span></div>
        <div class="flex justify-between"><span><?php echo e(__('Card')); ?></span><span class="tabular-nums"><?php echo e(number_format($metrics['card_sales'], 2)); ?></span></div>
        <div class="flex justify-between"><span><?php echo e(__('Bank')); ?></span><span class="tabular-nums"><?php echo e(number_format($metrics['bank_sales'], 2)); ?></span></div>
        <div class="flex justify-between font-semibold border-t border-erp-border pt-2"><span><?php echo e(__('Expected total')); ?></span><span class="tabular-nums"><?php echo e(number_format($metrics['expected_total'], 2)); ?></span></div>
    </div>

    <h3 class="text-sm font-semibold mb-2"><?php echo e(__('Cash reconciliation')); ?></h3>
    <div class="space-y-1 text-sm">
        <div class="flex justify-between"><span><?php echo e(__('Expected cash')); ?></span><span class="tabular-nums"><?php echo e(number_format($session->expected_cash ?? $metrics['expected_closing_cash'], 2)); ?></span></div>
        <div class="flex justify-between"><span><?php echo e(__('Actual cash')); ?></span><span class="tabular-nums"><?php echo e($session->actual_cash !== null ? number_format($session->actual_cash, 2) : '—'); ?></span></div>
        <div class="flex justify-between font-semibold"><span><?php echo e(__('Variance')); ?></span><span class="tabular-nums"><?php echo e($session->variance !== null ? number_format($session->variance, 2) : '—'); ?></span></div>
        <p class="text-xs text-slate-500 pt-1"><?php echo e(__('Tolerance: :amount', ['amount' => number_format($varianceTolerance, 2)])); ?></p>
    </div>

    <?php if($session->variance_requires_approval): ?>
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
            <?php if($session->varianceApprover): ?>
                <?php echo e(__('Approved by :name on :date', ['name' => $session->varianceApprover->name, 'date' => $session->variance_approved_at?->format('Y-m-d H:i')])); ?>

            <?php else: ?>
                <?php echo e(__('Pending manager approval.')); ?>

            <?php endif; ?>
        </div>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\pos\sessions\partials\summary-body.blade.php ENDPATH**/ ?>