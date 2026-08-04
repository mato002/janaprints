<nav class="overflow-x-auto" aria-label="<?php echo e(__('Specification tabs')); ?>">
    <div class="flex min-w-max gap-1 border-b border-erp-border">
        <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a
                href="<?php echo e($tab['url']); ?>"
                class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'inline-flex min-h-[2.5rem] items-center border-b-2 px-3 text-sm font-medium whitespace-nowrap',
                    'border-erp-accent text-erp-accent' => $tab['active'],
                    'border-transparent text-slate-600 hover:text-slate-900' => ! $tab['active'],
                ]); ?>"
            ><?php echo e($tab['label']); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</nav>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\print-specifications\workspace\tabs-nav.blade.php ENDPATH**/ ?>