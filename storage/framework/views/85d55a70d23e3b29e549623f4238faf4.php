<section class="exec-kpi-strip" aria-label="<?php echo e(__('Executive KPIs')); ?>">
    <?php $__currentLoopData = $dashboard['kpi_strip']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $href = ! empty($kpi['route']) && Route::has($kpi['route']) ? route($kpi['route']) : null;
        ?>
        <?php if($href): ?>
            <a href="<?php echo e($href); ?>" data-turbo-frame="erp-main" data-turbo-action="advance" class="exec-kpi-cell exec-kpi-cell--link">
        <?php else: ?>
            <div class="exec-kpi-cell">
        <?php endif; ?>
            <span class="exec-kpi-cell__label"><?php echo e($kpi['label']); ?></span>
            <span class="exec-kpi-cell__value"><?php echo e($kpi['value']); ?></span>
            <?php if(! empty($kpi['hint'])): ?>
                <span class="exec-kpi-cell__hint"><?php echo e($kpi['hint']); ?></span>
            <?php endif; ?>
        <?php if($href): ?>
            </a>
        <?php else: ?>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\kpi-strip.blade.php ENDPATH**/ ?>