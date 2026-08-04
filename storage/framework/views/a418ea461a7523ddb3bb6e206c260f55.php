<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<nav class="production-floor-dept-segments mb-2" aria-label="<?php echo e(__('Department queues')); ?>">
    <?php $__currentLoopData = $departmentNav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(($item['slug'] ?? '') === '') continue; ?>
        <a
            href="<?php echo e(WorkspaceEmbed::url($item['url'])); ?>"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'production-floor-dept-segment',
                'production-floor-dept-segment--'.$item['slug'] => filled($item['slug'] ?? null),
                'production-floor-dept-segment--active' => $item['active'],
            ]); ?>"
            data-turbo-frame="<?php echo e(WorkspaceEmbed::turboFrame()); ?>"
            data-turbo-action="advance"
        ><?php echo e($item['label']); ?></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\queue\partials\department-nav.blade.php ENDPATH**/ ?>