<section class="ess-card">
    <h2 class="ess-section-title"><?php echo e(__('Onboarding progress')); ?></h2>
    <ol class="ess-timeline mt-4 space-y-4">
        <?php $__currentLoopData = $onboarding['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="<?php echo \Illuminate\Support\Arr::toCssClasses(['ess-timeline__item', 'ess-timeline__item--done' => $step['done']]); ?>">
                <span class="ess-timeline__dot" aria-hidden="true"></span>
                <div>
                    <p class="font-medium"><?php echo e($step['label']); ?></p>
                    <p class="text-sm text-erp-muted"><?php echo e($step['done'] ? __('Completed') : __('Pending')); ?></p>
                </div>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\ess\tabs\onboarding.blade.php ENDPATH**/ ?>