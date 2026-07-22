<?php
    $variant = $variant ?? 'neutral';
    $badgeClass = match ($variant) {
        'success' => 'erp-badge--success',
        'danger' => 'erp-badge--danger',
        'warning' => 'erp-badge--warning',
        'info' => 'erp-badge--info',
        'draft' => 'erp-badge--draft',
        default => 'erp-badge--neutral',
    };
?>

<span class="erp-badge <?php echo e($badgeClass); ?> text-xs whitespace-nowrap"><?php echo e($label); ?></span>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\queue\partials\status-badge.blade.php ENDPATH**/ ?>