<section class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-6">
    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <article class="rounded border border-erp-border px-3 py-2">
            <p class="text-xs text-slate-500"><?php echo e($item['label']); ?></p>
            <p class="truncate text-sm font-semibold tabular-nums text-slate-900"><?php echo e($item['value']); ?></p>
        </article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\print-specifications\workspace\summary-strip.blade.php ENDPATH**/ ?>