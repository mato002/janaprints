<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title"><?php echo e(__('Top Customers')); ?></h2></div>
    <div class="exec-table-scroll">
        <table class="exec-table">
            <thead>
                <tr>
                    <th><?php echo e(__('Customer')); ?></th>
                    <th class="text-right"><?php echo e(__('Orders')); ?></th>
                    <th class="text-right"><?php echo e(__('Revenue')); ?></th>
                    <th class="text-right"><?php echo e(__('Outstanding')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $dashboard['top_customers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <?php if($customer['route']): ?>
                                <a href="<?php echo e($customer['route']); ?>" data-turbo-frame="erp-main" class="font-medium text-erp-accent hover:underline"><?php echo e($customer['name']); ?></a>
                            <?php else: ?>
                                <?php echo e($customer['name']); ?>

                            <?php endif; ?>
                        </td>
                        <td class="text-right tabular-nums"><?php echo e($customer['orders']); ?></td>
                        <td class="text-right tabular-nums"><?php echo e($customer['revenue']); ?></td>
                        <td class="text-right tabular-nums text-slate-500"><?php echo e($customer['outstanding']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="4" class="py-4 text-center text-xs text-slate-500"><?php echo e(__('No customer sales this month.')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/dashboard/partials/top-customers.blade.php ENDPATH**/ ?>