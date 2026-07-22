<section class="exec-panel exec-panel--insights" aria-label="<?php echo e(__('Smart insights')); ?>">
    <div class="exec-panel__head"><h2 class="exec-panel__title"><?php echo e(__('Smart Insights')); ?></h2></div>
    <ul class="space-y-1.5">
        <?php $__currentLoopData = $dashboard['insights']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $tone = match ($insight['tone']) {
                    'success' => 'text-emerald-700',
                    'danger' => 'text-red-700',
                    'warning' => 'text-amber-700',
                    'info' => 'text-sky-700',
                    default => 'text-slate-600',
                };
            ?>
            <li class="exec-insight <?php echo e($tone); ?>"><?php echo e($insight['text']); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\smart-insights.blade.php ENDPATH**/ ?>