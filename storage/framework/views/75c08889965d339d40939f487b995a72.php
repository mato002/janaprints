<?php
    $validationMessages = $errors->any()
        ? $errors->all()
        : array_filter([(string) (session('modal_error') ?? '')]);
?>

<?php if(count($validationMessages) > 0): ?>
    <div class="hidden" data-erp-validation-errors aria-hidden="true">
        <?php $__currentLoopData = $validationMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span data-erp-validation-message><?php echo e($error); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\partials\modal-validation-alert.blade.php ENDPATH**/ ?>