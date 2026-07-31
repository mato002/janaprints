<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'id',
    'name',
    'label',
    'required' => false,
    'full' => false,
    'options' => [],
    'placeholder' => 'Select an option',
    'value' => '',
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
    'required' => false,
    'full' => false,
    'options' => [],
    'placeholder' => 'Select an option',
    'value' => '',
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
    'public-field-float--select',
    'public-conversion-form__field',
    'public-conversion-form__field--full' => $full,
    'is-filled' => $hasValue,
]); ?>">
    <select
        id="<?php echo e($id); ?>"
        name="<?php echo e($name); ?>"
        <?php if($required): ?> required <?php endif; ?>
        <?php echo e($attributes->except(['class'])); ?>

    >
        <option value="" disabled <?php if(! $hasValue): echo 'selected'; endif; ?>><?php echo e($placeholder); ?></option>
        <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(is_array($option)): ?>
                <option value="<?php echo e($option['value']); ?>" <?php if($resolvedValue === $option['value']): echo 'selected'; endif; ?>><?php echo e($option['label']); ?></option>
            <?php else: ?>
                <option value="<?php echo e($option); ?>" <?php if($resolvedValue === $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>

    <label for="<?php echo e($id); ?>">
        <?php echo e($label); ?>

        <?php if($required): ?>
            <span class="public-field-float__required" aria-hidden="true">*</span>
        <?php endif; ?>
    </label>

    <?php if($hint): ?>
        <p class="public-field-float__hint"><?php echo e($hint); ?></p>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/public/form-floating-select.blade.php ENDPATH**/ ?>