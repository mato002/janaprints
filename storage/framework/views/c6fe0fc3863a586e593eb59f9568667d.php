<?php
    $next = $workspace['next_action'];
?>

<section class="qr-360__next-action qr-360__next-action--<?php echo e($next['tone']); ?>" aria-label="<?php echo e(__('Next recommended action')); ?>">
    <div>
        <p class="qr-360__next-label"><?php echo e(__('Next Recommended Action')); ?></p>
        <p class="qr-360__next-title"><?php echo e($next['label']); ?></p>
        <p class="qr-360__next-hint"><?php echo e($next['hint']); ?></p>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\next-action.blade.php ENDPATH**/ ?>