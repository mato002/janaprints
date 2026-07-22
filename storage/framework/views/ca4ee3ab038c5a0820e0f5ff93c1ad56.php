<?php
    $priorityKey = $priority ?? 'normal';
    $classes = match ($priorityKey) {
        'low' => 'production-floor-priority--normal',
        'normal' => 'production-floor-priority--normal',
        'high' => 'production-floor-priority--high',
        'urgent' => 'production-floor-priority--urgent',
        'critical' => 'production-floor-priority--critical',
        default => 'production-floor-priority--normal',
    };
    $icon = match ($priorityKey) {
        'high' => '🟡',
        'urgent' => '🟠',
        'critical' => '🔴',
        default => '🟢',
    };
?>
<span class="production-floor-priority <?php echo e($classes); ?>" <?php if(! empty($priorityKey)): ?> data-priority="<?php echo e($priorityKey); ?>" <?php endif; ?>>
    <span class="production-floor-priority__icon" aria-hidden="true"><?php echo e($icon); ?></span>
    <span class="production-floor-priority__label"><?php echo e($label ?? ucfirst($priorityKey)); ?></span>
</span>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\floor\partials\priority-badge.blade.php ENDPATH**/ ?>