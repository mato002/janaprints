<?php $sales = $dashboard['sales']; ?>
<section class="exec-panel">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Sales Performance')); ?></h2>
        <span class="text-[11px] text-slate-500"><?php echo e(__('30 days')); ?> · <?php echo e($sales['revenue_trend']); ?></span>
    </div>
    <div class="exec-bar-chart" role="img" aria-label="<?php echo e(__('Sales last 30 days')); ?>">
        <?php $__currentLoopData = $sales['chart']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="exec-bar-chart__col" title="<?php echo e($point['label']); ?>: <?php echo e(number_format($point['value'], 0)); ?>">
                <div class="exec-bar-chart__bar" style="height: <?php echo e(max($point['percent'], 2)); ?>%"></div>
                <?php if($loop->index % 5 === 0): ?>
                    <span class="exec-bar-chart__label"><?php echo e($point['label']); ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="exec-metric-grid exec-metric-grid--3 mt-2">
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Quotes MTD')); ?></span><span class="exec-metric__value"><?php echo e($sales['quotes_mtd']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Orders MTD')); ?></span><span class="exec-metric__value"><?php echo e($sales['orders_mtd']); ?></span></div>
        <div class="exec-metric"><span class="exec-metric__label"><?php echo e(__('Conversion')); ?></span><span class="exec-metric__value"><?php echo e($sales['conversion_rate']); ?>%</span></div>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\sales-performance.blade.php ENDPATH**/ ?>