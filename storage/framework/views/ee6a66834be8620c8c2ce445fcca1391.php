<nav class="mb-4 flex gap-2 overflow-x-auto pb-1" aria-label="<?php echo e(__('Department queues')); ?>">
    <?php $__currentLoopData = $departmentNav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a
            href="<?php echo e($item['url']); ?>"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'shrink-0 rounded-full border px-3 py-1.5 text-sm font-medium transition-colors',
                'border-erp-primary bg-erp-primary/10 text-erp-primary' => $item['active'],
                'border-erp-border bg-white text-slate-600 hover:border-erp-primary/40 hover:text-erp-primary' => ! $item['active'],
            ]); ?>"
            data-turbo-frame="erp-main"
        >
            <?php echo e($item['label']); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/queue/partials/department-nav.blade.php ENDPATH**/ ?>