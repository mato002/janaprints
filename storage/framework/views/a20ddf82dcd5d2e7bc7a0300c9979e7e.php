<?php
    $employee = $employee ?? null;
?>

<div class="md:col-span-2 mt-2 border-t border-erp-border pt-4">
    <h3 class="text-sm font-semibold text-erp-primary"><?php echo e($title); ?></h3>
    <?php if(! empty($description)): ?>
        <p class="mt-1 text-xs text-slate-500"><?php echo e($description); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\employees\partials\form-section-heading.blade.php ENDPATH**/ ?>