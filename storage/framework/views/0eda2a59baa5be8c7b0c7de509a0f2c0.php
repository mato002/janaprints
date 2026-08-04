<dl class="divide-y divide-erp-border rounded-lg border border-erp-border text-sm">
    <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="flex justify-between gap-4 px-3 py-2.5">
            <dt class="shrink-0 text-slate-500"><?php echo e($field['label']); ?></dt>
            <dd class="text-right font-medium text-slate-900"><?php echo e($field['value'] ?? '—'); ?></dd>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</dl>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/manufacturing-field-list.blade.php ENDPATH**/ ?>