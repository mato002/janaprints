<?php
    $stageKey = $stage ?? '';
    $classes = match ($stageKey) {
        'waiting' => 'production-floor-stage--waiting',
        'on_press' => 'production-floor-stage--printing',
        'at_vendor' => 'production-floor-stage--vendor',
        'finishing' => 'production-floor-stage--finishing',
        'qc' => 'production-floor-stage--qc',
        'ready' => 'production-floor-stage--dispatch',
        'out' => 'production-floor-stage--completed',
        'on_hold' => 'production-floor-stage--hold',
        default => 'production-floor-stage--waiting',
    };
?>
<span class="production-floor-stage <?php echo e($classes); ?>" <?php if(! empty($stageKey)): ?> data-stage="<?php echo e($stageKey); ?>" <?php endif; ?>>
    <?php echo e($label ?? '—'); ?>

</span>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/floor/partials/stage-badge.blade.php ENDPATH**/ ?>