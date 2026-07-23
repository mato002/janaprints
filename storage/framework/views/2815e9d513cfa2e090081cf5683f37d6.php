<nav class="mb-4 flex gap-2 overflow-x-auto pb-1" aria-label="<?php echo e(__('Operational registers')); ?>">
    <?php $__currentLoopData = $registers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $register): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $query = array_merge($filters, ['register' => $key]);
            if (request('embedded')) {
                $query['embedded'] = '1';
            }
        ?>
        <a
            href="<?php echo e(route('admin.reports.operational-registers', $query)); ?>"
            class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                'shrink-0 rounded-full border px-3 py-1.5 text-sm font-medium transition-colors',
                'border-erp-primary bg-erp-primary/10 text-erp-primary' => $active_register === $key,
                'border-erp-border bg-white text-slate-600 hover:border-erp-primary/40 hover:text-erp-primary' => $active_register !== $key,
            ]); ?>"
            data-turbo-frame="<?php echo e($turbo_frame); ?>"
        >
            <?php echo e($register['label']); ?>

        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\reports\operational-registers\partials\register-nav.blade.php ENDPATH**/ ?>