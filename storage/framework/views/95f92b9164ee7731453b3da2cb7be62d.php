<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status' => null, 'label' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['status' => null, 'label' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $value = is_object($status) && property_exists($status, 'value') ? $status->value : (string) ($status ?? $label ?? '');
    $display = $label ?? str($value)->headline();
    $tone = match (strtolower($value)) {
        'sent', 'viewed', 'submitted', 'in_progress', 'in progress' => 'info',
        'accepted', 'approved', 'posted', 'delivered', 'closed', 'paid' => 'success',
        'rejected', 'cancelled', 'declined', 'overdue' => 'danger',
        'revision_requested', 'revision requested', 'pending', 'pending_approval', 'pending approval' => 'warning',
        default => 'neutral',
    };
?>

<span <?php echo e($attributes->merge(['class' => 'client-badge client-badge--'.$tone])); ?>><?php echo e($display); ?></span>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\client\partials\status-badge.blade.php ENDPATH**/ ?>