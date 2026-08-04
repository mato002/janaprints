<?php
    $display = $empty ?? '—';
?>

<?php if($value === null || $value === ''): ?>
    <span class="text-slate-400"><?php echo e($display); ?></span>
<?php elseif($type === 'boolean'): ?>
    <?php echo e(filter_var($value, FILTER_VALIDATE_BOOLEAN) ? __('Yes') : __('No')); ?>

<?php else: ?>
    <?php echo e($value); ?>

<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\settings\partials\setting-display.blade.php ENDPATH**/ ?>