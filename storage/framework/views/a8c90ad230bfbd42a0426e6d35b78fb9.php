<?php
    $hr = $dashboard['hr_snapshot'] ?? null;
    $links = $hr['links'] ?? [];
?>

<?php if(! empty($hr['visible'])): ?>
    <section class="exec-panel exec-panel--hr">
        <div class="exec-panel__head exec-panel__head--split">
            <h2 class="exec-panel__title"><?php echo e(__('HR Snapshot')); ?></h2>
            <?php if($links !== []): ?>
                <nav class="exec-finance-links" aria-label="<?php echo e(__('HR intelligence')); ?>">
                    <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e($link['url']); ?>" data-turbo-frame="erp-main" class="exec-finance-links__item"><?php echo e($link['label']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </nav>
            <?php endif; ?>
        </div>
        <dl class="exec-dl exec-dl--grid">
            <div class="exec-dl__row"><dt><?php echo e(__('Employees')); ?></dt><dd><?php echo e($hr['employees'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Present Today')); ?></dt><dd><?php echo e($hr['present_today'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Attendance %')); ?></dt><dd><?php echo e($hr['attendance_percent'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Employees On Leave')); ?></dt><dd><?php echo e($hr['on_leave'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Payroll Cost MTD')); ?></dt><dd><?php echo e($hr['payroll_cost_mtd'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Overtime Cost')); ?></dt><dd><?php echo e($hr['overtime_cost'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Contract Expiry')); ?></dt><dd><?php echo e($hr['contract_expiry'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Training Due')); ?></dt><dd><?php echo e($hr['training_due'] ?? '—'); ?></dd></div>
            <div class="exec-dl__row"><dt><?php echo e(__('Performance Alerts')); ?></dt><dd><?php echo e($hr['performance_alerts'] ?? '—'); ?></dd></div>
        </dl>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\hr-snapshot.blade.php ENDPATH**/ ?>