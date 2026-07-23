<?php
    use App\Enums\SalesOrderStatus;
?>

<div class="so-360__grid so-360__grid--two">
    <article class="so-360__card">
        <div class="so-360__card-head">
            <h2 class="so-360__card-title"><?php echo e(__('Invoicing')); ?></h2>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Sales\CustomerInvoice::class)): ?>
                <?php if($salesOrder->remainingInvoiceTotal() > 0 && ! in_array($salesOrder->status, [SalesOrderStatus::Draft, SalesOrderStatus::Cancelled], true)): ?>
                    <a href="<?php echo e(route('admin.invoices.from-sales-order', $salesOrder)); ?>" class="erp-btn-primary" data-erp-modal-open><?php echo e(__('Create invoice')); ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <?php if(! empty($financial['billing_eligibility']['blockers'])): ?>
            <p class="mb-3 text-sm text-amber-700"><?php echo e(implode(' ', $financial['billing_eligibility']['blockers'])); ?></p>
            <p class="mb-3 text-xs text-amber-700"><?php echo e(__('Use deposit or progress billing on the next screen if you need to invoice before fulfilment is complete.')); ?></p>
        <?php endif; ?>

        <dl class="so-360__dl so-360__dl--compact">
            <div><dt><?php echo e(__('Order total')); ?></dt><dd class="font-mono"><?php echo e(number_format($salesOrder->total_amount, 2)); ?></dd></div>
            <div><dt><?php echo e(__('Invoiced')); ?></dt><dd class="font-mono"><?php echo e(number_format($salesOrder->invoiced_total, 2)); ?></dd></div>
            <div><dt><?php echo e(__('Remaining')); ?></dt><dd class="font-mono"><?php echo e(number_format($salesOrder->remainingInvoiceTotal(), 2)); ?></dd></div>
        </dl>

        <?php if(! empty($financial['payment'])): ?>
            <dl class="so-360__dl so-360__dl--compact mt-3 border-t border-slate-100 pt-3">
                <div><dt><?php echo e(__('Payment status')); ?></dt><dd><?php echo e($financial['payment']['label']); ?></dd></div>
                <div><dt><?php echo e(__('Paid')); ?></dt><dd class="font-mono"><?php echo e(number_format($financial['payment']['amount_paid'], 2)); ?></dd></div>
                <div><dt><?php echo e(__('Outstanding')); ?></dt><dd class="font-mono"><?php echo e(number_format($financial['payment']['amount_outstanding'], 2)); ?></dd></div>
                <div><dt><?php echo e(__('Fulfilment ready')); ?></dt><dd><?php echo e(($financial['billing_eligibility']['fulfilment_ready'] ?? false) ? __('Yes') : __('No')); ?></dd></div>
            </dl>
        <?php endif; ?>
    </article>

    <article class="so-360__card">
        <h2 class="so-360__card-title"><?php echo e(__('Billing & deposit')); ?></h2>
        <?php if(! empty($financial['deposit'])): ?>
            <dl class="so-360__dl so-360__dl--compact">
                <div><dt><?php echo e(__('Billing type')); ?></dt><dd><?php echo e($financial['deposit']['billing_type']); ?></dd></div>
                <div><dt><?php echo e(__('Required deposit')); ?></dt><dd class="font-mono"><?php echo e(number_format($financial['deposit']['required'], 2)); ?></dd></div>
                <div><dt><?php echo e(__('Deposit invoiced')); ?></dt><dd class="font-mono"><?php echo e(number_format($financial['deposit']['invoiced'], 2)); ?></dd></div>
                <div><dt><?php echo e(__('Deposit paid')); ?></dt><dd class="font-mono"><?php echo e(number_format($financial['deposit']['paid'], 2)); ?></dd></div>
            </dl>
        <?php else: ?>
            <p class="text-sm text-slate-500"><?php echo e(__('No deposit terms on this order.')); ?></p>
        <?php endif; ?>

        <?php if($salesOrder->invoices->isNotEmpty()): ?>
            <ul class="mt-4 space-y-1.5 text-sm">
                <?php $__currentLoopData = $salesOrder->invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $inv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 py-1.5 last:border-0">
                        <a href="<?php echo e(route('admin.invoices.show', $inv)); ?>" class="so-360__link font-mono" data-turbo-frame="erp-main"><?php echo e($inv->invoice_number); ?></a>
                        <span class="text-slate-600"><?php echo e($inv->status->label()); ?> · <span class="font-mono"><?php echo e(number_format($inv->total_amount, 2)); ?></span></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php endif; ?>
    </article>
</div>

<?php if(! empty($profitability)): ?>
    <details class="so-360__collapse so-360__collapse--block mt-4">
        <summary><?php echo e(__('Estimated profitability breakdown')); ?></summary>
        <div class="so-360__collapse-body">
            <dl class="so-360__dl so-360__dl--compact sm:!grid-cols-3 lg:!grid-cols-6">
                <div><dt><?php echo e(__('Revenue')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['revenue'], 2)); ?></dd></div>
                <div><dt><?php echo e(__('Material cost')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['material_cost'], 2)); ?></dd></div>
                <div><dt><?php echo e(__('Waste cost')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['wastage_cost'], 2)); ?></dd></div>
                <div><dt><?php echo e(__('Outsource cost')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['outsource_cost'], 2)); ?></dd></div>
                <div><dt><?php echo e(__('Estimated profit')); ?></dt><dd class="font-mono"><?php echo e(number_format($profitability['estimated_profit'], 2)); ?></dd></div>
                <div><dt><?php echo e(__('Margin')); ?></dt><dd><?php echo e(number_format($profitability['estimated_margin_percent'], 1)); ?>%</dd></div>
            </dl>
        </div>
    </details>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/orders/workspace/tabs/financial.blade.php ENDPATH**/ ?>