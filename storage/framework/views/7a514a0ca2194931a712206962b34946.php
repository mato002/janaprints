<?php if(session('status')): ?>
    <div hidden data-erp-flash-status><?php echo e(session('status')); ?></div>
<?php endif; ?>

<?php if(session('error')): ?>
    <div hidden data-erp-flash-error><?php echo e(session('error')); ?></div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div hidden data-erp-validation-errors>
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span><?php echo e($error); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/partials/alerts.blade.php ENDPATH**/ ?>