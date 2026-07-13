<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<?php if(! empty($workspaceNavigation)): ?>
    <?php echo $__env->make('admin.partials.workspace-back', ['compact' => ! empty($compact)], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<?php if(! empty($breadcrumbs)): ?>
    <nav class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'text-slate-500',
        'mb-2 text-xs' => ! empty($compact),
        'mb-4 text-sm' => empty($compact),
    ]); ?>" aria-label="<?php echo e(__('Breadcrumb')); ?>">
        <ol class="flex flex-wrap items-center gap-1.5">
            <li>
                <a href="<?php echo e(route('admin.dashboard')); ?>" data-turbo-frame="erp-main" data-turbo-action="advance" class="font-medium transition-colors hover:text-erp-accent"><?php echo e(__('Dashboard')); ?></a>
            </li>
            <?php $__currentLoopData = $breadcrumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $crumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center gap-1.5" aria-current="<?php echo e(empty($crumb['url']) ? 'page' : false); ?>">
                    <span class="text-slate-300" aria-hidden="true">/</span>
                    <?php if(! empty($crumb['url'])): ?>
                        <a href="<?php echo e(WorkspaceEmbed::url($crumb['url']) ?? $crumb['url']); ?>" data-turbo-frame="erp-main" data-turbo-action="advance" class="transition-colors hover:text-erp-accent"><?php echo e($crumb['label']); ?></a>
                    <?php else: ?>
                        <span class="font-medium text-erp-primary"><?php echo e($crumb['label']); ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>
    </nav>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/partials/breadcrumbs.blade.php ENDPATH**/ ?>