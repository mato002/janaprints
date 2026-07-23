<div class="so-360__grid so-360__grid--two">
    <article class="so-360__card">
        <h2 class="so-360__card-title"><?php echo e(__('Customer & commercial')); ?></h2>
        <dl class="so-360__dl so-360__dl--compact">
            <div>
                <dt><?php echo e(__('Customer')); ?></dt>
                <dd>
                    <?php if($salesOrder->customer): ?>
                        <a href="<?php echo e(route('admin.crm.customers.show', $salesOrder->customer)); ?>" class="so-360__link" data-turbo-frame="erp-main">
                            <?php echo e($salesOrder->customer->company_name); ?>

                        </a>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt><?php echo e(__('Quotation')); ?></dt>
                <dd>
                    <?php if($salesOrder->quotation): ?>
                        <a href="<?php echo e(route('admin.quotations.show', $salesOrder->quotation)); ?>" class="so-360__link" data-turbo-frame="erp-main">
                            <?php echo e($salesOrder->quotation->quotation_number); ?>

                        </a>
                    <?php else: ?>
                        <?php echo e($salesOrder->is_direct_order ? __('Direct order') : '—'); ?>

                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt><?php echo e(__('Created by')); ?></dt>
                <dd><?php echo e($salesOrder->creator?->name ?? '—'); ?></dd>
            </div>
            <div>
                <dt><?php echo e(__('Branch')); ?></dt>
                <dd><?php echo e($salesOrder->branch?->name ?? '—'); ?></dd>
            </div>
        </dl>
    </article>

    <article class="so-360__card">
        <h2 class="so-360__card-title"><?php echo e(__('Totals')); ?></h2>
        <div class="so-360__kpi-row">
            <div class="so-360__kpi">
                <span class="so-360__kpi-label"><?php echo e(__('Subtotal')); ?></span>
                <span class="so-360__kpi-value font-mono"><?php echo e(number_format($salesOrder->subtotal, 2)); ?></span>
            </div>
            <div class="so-360__kpi">
                <span class="so-360__kpi-label"><?php echo e(__('Tax')); ?></span>
                <span class="so-360__kpi-value font-mono"><?php echo e(number_format($salesOrder->tax_amount, 2)); ?></span>
            </div>
            <div class="so-360__kpi so-360__kpi--emphasis">
                <span class="so-360__kpi-label"><?php echo e(__('Total')); ?></span>
                <span class="so-360__kpi-value font-mono"><?php echo e(number_format($salesOrder->total_amount, 2)); ?></span>
            </div>
        </div>
    </article>
</div>

<article class="so-360__card mt-4">
    <div class="so-360__card-head">
        <h2 class="so-360__card-title"><?php echo e(__('Line items')); ?></h2>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $salesOrder)): ?>
            <a href="<?php echo e(route('admin.sales-orders.edit', $salesOrder)); ?>" class="so-360__text-btn"><?php echo e(__('Edit order')); ?></a>
        <?php endif; ?>
    </div>
    <?php $__empty_1 = true; $__currentLoopData = $salesOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php echo $__env->make('admin.sales.orders.partials.item-specification', [
            'salesOrder' => $salesOrder,
            'item' => $item,
            'itemSpecifications' => $itemSpecifications ?? [],
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-sm text-slate-500"><?php echo e(__('No line items.')); ?></p>
    <?php endif; ?>
</article>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/orders/workspace/tabs/commercial.blade.php ENDPATH**/ ?>