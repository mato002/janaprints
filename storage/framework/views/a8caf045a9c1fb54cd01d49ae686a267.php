<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'cancelUrl' => null,
]));

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

foreach (array_filter(([
    'cancelUrl' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $inFormModal = request()->header('Turbo-Frame') === 'erp-form-modal';
?>

<div <?php echo e($attributes->class(['erp-form-modal__actions'])); ?>>
    <?php if($inFormModal): ?>
        <button type="button" class="erp-btn-secondary" data-erp-form-modal-close>
            <?php echo e(__('Cancel')); ?>

        </button>
    <?php elseif($cancelUrl): ?>
        <a href="<?php echo e($cancelUrl); ?>" class="erp-btn-secondary"><?php echo e(__('Cancel')); ?></a>
    <?php endif; ?>
    <?php echo e($slot); ?>

</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/form-actions.blade.php ENDPATH**/ ?>