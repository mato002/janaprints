<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['name' => null, 'messages' => null]));

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

foreach (array_filter((['name' => null, 'messages' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $resolvedMessages = $messages;

    if ($resolvedMessages === null && $name !== null) {
        $resolvedMessages = $errors->get($name);
    }
?>

<?php if($resolvedMessages): ?>
    <x-input-error
        :messages="$resolvedMessages"
        <?php echo e($attributes->merge(['class' => 'mt-1'])); ?>

        <?php if($name): ?> data-erp-field-error="<?php echo e($name); ?>" <?php endif; ?>
    />
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/field-error.blade.php ENDPATH**/ ?>