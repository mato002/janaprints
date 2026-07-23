<section class="exec-panel">
    <div class="exec-panel__head"><h2 class="exec-panel__title"><?php echo e(__('Quick Actions')); ?></h2></div>
    <div class="exec-quick-actions">
        <?php $__currentLoopData = $dashboard['quick_actions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(! empty($action['coming_soon'])): ?>
                <span class="exec-quick-btn exec-quick-btn--disabled" title="<?php echo e(__('Coming soon')); ?>"><?php echo e($action['label']); ?></span>
            <?php elseif(! empty($action['route']) && Route::has($action['route'])): ?>
                <?php if(empty($action['permission']) || auth()->user()?->can($action['permission'])): ?>
                    <a href="<?php echo e(route($action['route'])); ?>" data-turbo-frame="erp-main" class="exec-quick-btn"><?php echo e($action['label']); ?></a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\quick-actions.blade.php ENDPATH**/ ?>