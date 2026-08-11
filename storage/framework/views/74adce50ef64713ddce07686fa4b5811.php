<?php if(session('status') || session('success') || session('message')): ?>
    <div hidden data-erp-flash-status><?php echo e(session('status') ?? session('success') ?? session('message')); ?></div>
<?php endif; ?>

<?php if(session('error') || session('danger')): ?>
    <div hidden data-erp-flash-error><?php echo e(session('error') ?? session('danger')); ?></div>
<?php endif; ?>

<?php if(session('warning')): ?>
    <div hidden data-erp-flash-warning><?php echo e(session('warning')); ?></div>
<?php endif; ?>

<?php if(session('info')): ?>
    <div hidden data-erp-flash-info><?php echo e(session('info')); ?></div>
<?php endif; ?>


<?php if(session('inbox_reply_sent')): ?>
    <div hidden data-erp-flash-status><?php echo e(__('Message sent.')); ?></div>
<?php endif; ?>

<?php if(session('inbox_attachment_sent')): ?>
    <div hidden data-erp-flash-status><?php echo e(__('Attachment uploaded.')); ?></div>
<?php endif; ?>

<?php if(($errors ?? null)?->any()): ?>
    <div hidden data-erp-validation-errors>
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span data-erp-validation-message><?php echo e($error); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\partials\alerts.blade.php ENDPATH**/ ?>