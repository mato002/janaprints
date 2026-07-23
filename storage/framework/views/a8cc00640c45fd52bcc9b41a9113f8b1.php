<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id',
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'optional' => false,
    'full' => false,
    'value' => '',
    'rows' => 4,
    'autocomplete' => null,
    'inputmode' => null,
    'maxlength' => null,
    'hint' => null,
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
    'id',
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'optional' => false,
    'full' => false,
    'value' => '',
    'rows' => 4,
    'autocomplete' => null,
    'inputmode' => null,
    'maxlength' => null,
    'hint' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $resolvedValue = old($name, $value);
    $hasValue = filled($resolvedValue);
?>

<div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
    'public-field-float',
    'public-conversion-form__field',
    'public-conversion-form__field--full' => $full,
    'is-filled' => $hasValue,
]); ?>">
    <?php if($type === 'textarea'): ?>
        <textarea
            id="<?php echo e($id); ?>"
            name="<?php echo e($name); ?>"
            rows="<?php echo e($rows); ?>"
            placeholder=" "
            <?php if($required): ?> required <?php endif; ?>
            <?php if($autocomplete): ?> autocomplete="<?php echo e($autocomplete); ?>" <?php endif; ?>
            <?php if($maxlength): ?> maxlength="<?php echo e($maxlength); ?>" <?php endif; ?>
            <?php echo e($attributes->except(['class'])); ?>

        ><?php echo e($resolvedValue); ?></textarea>
    <?php else: ?>
        <input
            type="<?php echo e($type); ?>"
            id="<?php echo e($id); ?>"
            name="<?php echo e($name); ?>"
            value="<?php echo e($resolvedValue); ?>"
            placeholder=" "
            <?php if($required): ?> required <?php endif; ?>
            <?php if($autocomplete): ?> autocomplete="<?php echo e($autocomplete); ?>" <?php endif; ?>
            <?php if($inputmode): ?> inputmode="<?php echo e($inputmode); ?>" <?php endif; ?>
            <?php if($maxlength): ?> maxlength="<?php echo e($maxlength); ?>" <?php endif; ?>
            <?php echo e($attributes->except(['class'])); ?>

        >
    <?php endif; ?>

    <label for="<?php echo e($id); ?>">
        <?php echo e($label); ?>

        <?php if($required): ?>
            <span class="public-field-float__required" aria-hidden="true">*</span>
        <?php elseif($optional): ?>
            <span class="public-field-float__optional">(optional)</span>
        <?php endif; ?>
    </label>

    <?php if($hint): ?>
        <p class="public-field-float__hint"><?php echo e($hint); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/form-floating-field.blade.php ENDPATH**/ ?>