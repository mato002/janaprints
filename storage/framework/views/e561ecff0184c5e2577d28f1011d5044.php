<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $groups = $workspace['tab_groups'] ?? ['primary' => $tabs, 'more' => [], 'more_open' => false];
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
?>

<div class="c360-tabs-shell" aria-label="<?php echo e(__('Job workspace tabs')); ?>">
    <nav class="c360-tabs c360-tabs--compact c360-tabs--stretch c360-tabs--scroll">
        <?php $__currentLoopData = $groups['primary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e($tab['url']); ?>"
                class="c360-tabs__link <?php echo e($tab['active'] ? 'c360-tabs__link--active' : ''); ?>"
                <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($tab['active']): ?> aria-current="page" <?php endif; ?>
            ><?php echo e($tab['label']); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>

    <?php if(! empty($groups['more'])): ?>
        <details class="c360-tabs__more" <?php if($groups['more_open'] ?? false): ?> open <?php endif; ?>>
            <summary class="c360-tabs__link c360-tabs__link--more flex h-full cursor-pointer list-none items-center [&::-webkit-details-marker]:hidden <?php echo e(collect($groups['more'])->contains('active', true) ? 'c360-tabs__link--active' : ''); ?>">
                <?php echo e(__('More')); ?>

            </summary>
            <div class="c360-tabs__more-menu" role="menu">
                <?php $__currentLoopData = $groups['more']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a
                        href="<?php echo e($tab['url']); ?>"
                        class="c360-tabs__more-link <?php echo e($tab['active'] ? 'c360-tabs__more-link--active' : ''); ?>"
                        role="menuitem"
                        <?php $__currentLoopData = $linkTurboAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    ><?php echo e($tab['label']); ?></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </details>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs-nav.blade.php ENDPATH**/ ?>