<section class="space-y-3">
    <?php $__empty_1 = true; $__currentLoopData = $payrollHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="ess-card">
            <div class="flex flex-col gap-2">
                <p class="font-semibold">
                    <?php echo e($row['period_start']?->format('d M Y') ?? '—'); ?>

                    —
                    <?php echo e($row['period_end']?->format('d M Y') ?? '—'); ?>

                </p>
                <dl class="ess-dl ess-dl--compact">
                    <div><dt><?php echo e(__('Gross pay')); ?></dt><dd>KES <?php echo e(number_format($row['gross_pay'], 2)); ?></dd></div>
                    <div><dt><?php echo e(__('Deductions')); ?></dt><dd>KES <?php echo e(number_format($row['total_deductions'], 2)); ?></dd></div>
                    <div><dt><?php echo e(__('Net pay')); ?></dt><dd>KES <?php echo e(number_format($row['net_pay'], 2)); ?></dd></div>
                    <div><dt><?php echo e(__('Payment status')); ?></dt><dd><?php echo e($row['payment_status']); ?></dd></div>
                    <div><dt><?php echo e(__('Payment date')); ?></dt><dd><?php echo e($row['pay_date']?->format('d M Y') ?? '—'); ?></dd></div>
                </dl>
            </div>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="ess-card text-sm text-erp-muted"><?php echo e(__('No payroll history available.')); ?></div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\ess\tabs\payroll-history.blade.php ENDPATH**/ ?>