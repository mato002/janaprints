<div class="overflow-x-auto">
    <table class="erp-table text-sm">
        <thead>
            <tr>
                <th><?php echo e(__('Customer')); ?></th>
                <th><?php echo e(__('Revenue')); ?></th>
                <th><?php echo e(__('Profit')); ?></th>
                <th><?php echo e(__('Margin')); ?></th>
                <th><?php echo e(__('Profit / job')); ?></th>
                <th><?php echo e(__('Jobs')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($row['customer_name'] ?? '—'); ?></td>
                    <td><?php echo e(number_format((float) ($row['revenue'] ?? 0), 2)); ?></td>
                    <td><?php echo e(number_format((float) ($row['profit'] ?? 0), 2)); ?></td>
                    <td><?php echo e(($row['margin_percent'] ?? null) !== null ? number_format((float) $row['margin_percent'], 1).'%' : '—'); ?></td>
                    <td><?php echo e(($row['profit_per_job'] ?? null) !== null ? number_format((float) $row['profit_per_job'], 2) : '—'); ?></td>
                    <td><?php echo e($row['jobs_completed'] ?? 0); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center text-slate-500 py-6"><?php echo e(__('No customer profitability data yet.')); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\printing-intelligence\partials\profitability-customers-table.blade.php ENDPATH**/ ?>