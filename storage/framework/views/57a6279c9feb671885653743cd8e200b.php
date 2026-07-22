<?php $prod = $dashboard['production']; ?>
<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title"><?php echo e(__('Production Performance')); ?></h2></div>
    <div class="exec-metric-grid exec-metric-grid--3">
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Completed MTD')); ?></span><span class="exec-metric__value"><?php echo e($prod['completed_mtd']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Avg turnaround')); ?></span><span class="exec-metric__value"><?php echo e($prod['avg_turnaround']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Delayed')); ?></span><span class="exec-metric__value text-red-600"><?php echo e($prod['delayed']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('In progress')); ?></span><span class="exec-metric__value"><?php echo e($prod['in_progress']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Machine utilization')); ?></span><span class="exec-metric__value"><?php echo e($prod['machine_utilization']); ?>%</span></div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\production-performance.blade.php ENDPATH**/ ?>