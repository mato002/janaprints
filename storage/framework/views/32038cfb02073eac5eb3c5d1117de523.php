<?php
    $validationMessages = $validationMessages
        ?? (($errors ?? null)?->any() ? $errors->all() : []);
    $validationMessages = is_array($validationMessages)
        ? array_values(array_filter($validationMessages, fn ($message) => filled($message)))
        : [];
    if ($validationMessages === [] && filled(session('modal_error'))) {
        $validationMessages = [(string) session('modal_error')];
    }
    $validationPresentation = $validationPresentation ?? session('form_error_presentation');
?>

<?php if(count($validationMessages) > 0): ?>
    <div
        class="hidden"
        data-erp-validation-errors
        aria-hidden="true"
        <?php if(! empty($validationPresentation['category'])): ?>
            data-erp-validation-category="<?php echo e($validationPresentation['category']); ?>"
        <?php endif; ?>
        <?php if(! empty($validationPresentation['category_label'])): ?>
            data-erp-validation-category-label="<?php echo e($validationPresentation['category_label']); ?>"
        <?php endif; ?>
    >
        <?php $__currentLoopData = $validationMessages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span data-erp-validation-message><?php echo e($error); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/partials/modal-validation-alert.blade.php ENDPATH**/ ?>