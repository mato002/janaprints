<?php if($errors->has('workflow') || $errors->has('status')): ?>
    <div class="mb-3 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
        <?php echo e($errors->first('workflow') ?: $errors->first('status')); ?>

    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/workflow-error.blade.php ENDPATH**/ ?>