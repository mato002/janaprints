<?php
    $groups = $workspace['tab_groups'] ?? ['primary' => $tabs, 'more' => [], 'more_open' => false];
?>

<nav class="c360-tabs" aria-label="<?php echo e(__('Job workspace tabs')); ?>">
    <?php $__currentLoopData = $groups['primary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a
            href="<?php echo e($tab['url']); ?>"
            class="c360-tabs__link <?php echo e($tab['active'] ? 'c360-tabs__link--active' : ''); ?>"
            data-turbo-frame="erp-main"
            data-turbo-action="advance"
            <?php if($tab['active']): ?> aria-current="page" <?php endif; ?>
        ><?php echo e($tab['label']); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if(! empty($groups['more'])): ?>
        <details class="c360-tabs__more inline-block" <?php if($groups['more_open'] ?? false): ?> open <?php endif; ?>>
            <summary class="c360-tabs__link cursor-pointer list-none <?php echo e(collect($groups['more'])->contains('active', true) ? 'c360-tabs__link--active' : ''); ?>">
                <?php echo e(__('More')); ?>

            </summary>
            <div class="mt-1 flex flex-wrap gap-1 rounded-lg border border-erp-border bg-white p-2 shadow-sm">
                <?php $__currentLoopData = $groups['more']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a
                        href="<?php echo e($tab['url']); ?>"
                        class="rounded px-2 py-1 text-xs <?php echo e($tab['active'] ? 'bg-erp-accent/10 font-semibold text-erp-accent' : 'text-slate-600 hover:bg-slate-50'); ?>"
                        data-turbo-frame="erp-main"
                        data-turbo-action="advance"
                    ><?php echo e($tab['label']); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </details>
    <?php endif; ?>
</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/tabs-nav.blade.php ENDPATH**/ ?>