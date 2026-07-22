<?php if(! empty($tab_data['summary'])): ?>
    <div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
        <?php $__currentLoopData = $tab_data['summary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-md border border-erp-border bg-white px-3 py-2">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500"><?php echo e($item['label']); ?></p>
                <p class="mt-0.5 text-lg font-semibold tabular-nums text-erp-primary"><?php echo e($item['value']); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<?php echo $__env->make('admin.reports.operational-registers.partials.register-table', [
    'table' => $tab_data['table'] ?? [],
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\reports\operational-registers\partials\register-content.blade.php ENDPATH**/ ?>