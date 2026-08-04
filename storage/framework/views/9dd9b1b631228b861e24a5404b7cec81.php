<div class="crm-360__tab-stack">
    <section class="crm-360__card">
        <div class="crm-360__card-head">
            <h2 class="crm-360__card-title"><?php echo e(__('Quotation list')); ?></h2>
            <div class="flex flex-wrap items-center gap-2">
                <?php echo $__env->make('admin.crm.leads.360.partials.quotation-actions', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>

        <?php if($quotationActions['auto_convert_enabled'] && $quotationActions['needs_customer']): ?>
            <p class="mb-4 text-sm text-slate-600"><?php echo e(__('Auto-convert is enabled. Creating a quotation will automatically create and link a customer from this lead.')); ?></p>
        <?php endif; ?>

        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('quotations.view')): ?>
            <table class="erp-table text-sm w-full">
                <thead>
                    <tr>
                        <th><?php echo e(__('Number')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <th><?php echo e(__('Total')); ?></th>
                        <th><?php echo e(__('Date')); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($quote['number']); ?></td>
                            <td><?php echo e($quote['status']); ?></td>
                            <td><?php echo e($quote['total']); ?></td>
                            <td><?php echo e($quote['date']?->format('d M Y')); ?></td>
                            <td>
                                <a href="<?php echo e($quote['url']); ?>" class="text-erp-accent hover:underline text-sm" data-turbo-frame="erp-main"><?php echo e(__('View quotation')); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="text-slate-500 py-4"><?php echo e(__('No quotations linked to this lead yet')); ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="crm-360__empty-inline"><?php echo e(__('You do not have permission to view quotations')); ?></p>
        <?php endif; ?>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\leads\360\tab-quotations.blade.php ENDPATH**/ ?>