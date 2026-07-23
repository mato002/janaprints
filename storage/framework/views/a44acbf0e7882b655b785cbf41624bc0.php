<?php
    $totalPipeline = max(1, collect($dashboard['pipeline'])->sum('count'));
?>

<section class="exec-panel exec-panel--pipeline" aria-label="<?php echo e(__('Production command center')); ?>">
    <div class="exec-panel__head">
        <h2 class="exec-panel__title"><?php echo e(__('Production Command Center')); ?></h2>
        <span class="exec-panel__meta"><?php echo e(__('Live flow')); ?></span>
    </div>
    <div class="exec-pipeline-board">
        <?php $__currentLoopData = $dashboard['pipeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($index > 0): ?>
                <span class="exec-pipeline-board__connector" aria-hidden="true">
                    <span class="exec-pipeline-board__arrow-h">→</span>
                    <span class="exec-pipeline-board__arrow-v">↓</span>
                </span>
            <?php endif; ?>
            <?php
                $href = ! empty($stage['route']) && Route::has($stage['route']) ? route($stage['route']) : null;
                $flowPct = (int) round(($stage['count'] / $totalPipeline) * 100);
                $barPct = max($stage['percent'], $stage['count'] > 0 ? 12 : 6);
                $stageClass = 'exec-pipeline-board__stage--'.$stage['key'];
            ?>
            <?php if($href): ?>
                <a href="<?php echo e($href); ?>" data-turbo-frame="erp-main" class="exec-pipeline-board__stage exec-pipeline-board__stage--link <?php echo e($stageClass); ?>">
            <?php else: ?>
                <div class="exec-pipeline-board__stage <?php echo e($stageClass); ?>">
            <?php endif; ?>
                <span class="exec-pipeline-board__label"><?php echo e($stage['label']); ?></span>
                <span class="exec-pipeline-board__count"><?php echo e(number_format($stage['count'])); ?></span>
                <div class="exec-pipeline-board__track">
                    <div class="exec-pipeline-board__bar" style="width: <?php echo e($barPct); ?>%"></div>
                </div>
                <span class="exec-pipeline-board__flow"><?php echo e($flowPct); ?>% <?php echo e(__('of pipeline')); ?></span>
            <?php if($href): ?>
                </a>
            <?php else: ?>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\pipeline.blade.php ENDPATH**/ ?>