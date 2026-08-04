<nav class="c360-tabs" aria-label="<?php echo e(__('Customer workspace tabs')); ?>">
    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a
            href="<?php echo e($tab['url']); ?>"
            class="c360-tabs__link <?php echo e($tab['active'] ? 'c360-tabs__link--active' : ''); ?>"
            data-turbo-frame="erp-main"
            data-turbo-action="advance"
            <?php if($tab['active']): ?> aria-current="page" <?php endif; ?>
        ><?php echo e($tab['label']); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\workspace\tabs-nav.blade.php ENDPATH**/ ?>