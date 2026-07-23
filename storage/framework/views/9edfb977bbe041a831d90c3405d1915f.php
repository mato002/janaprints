<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'visible' => true,
    'readonly' => false,
    'help' => null,
    'placeholder' => null,
    'rows' => 3,
    'colSpan' => 2,
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
    'name',
    'label' => null,
    'value' => null,
    'required' => false,
    'visible' => true,
    'readonly' => false,
    'help' => null,
    'placeholder' => null,
    'rows' => 3,
    'colSpan' => 2,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.form-field','data' => ['name' => $name,'label' => $label,'required' => $required,'visible' => $visible,'readonly' => $readonly,'help' => $help,'colSpan' => $colSpan]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.form-field'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required),'visible' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($visible),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($readonly),'help' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($help),'colSpan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($colSpan)]); ?>
    <textarea
        id="<?php echo e($name); ?>"
        name="<?php echo e($name); ?>"
        rows="<?php echo e($rows); ?>"
        class="erp-input w-full"
        <?php if($placeholder): ?> placeholder="<?php echo e($placeholder); ?>" <?php endif; ?>
        <?php if($required): echo 'required'; endif; ?>
        <?php if($readonly): echo 'readonly'; endif; ?>
        <?php echo e($attributes); ?>

    ><?php echo e($value); ?></textarea>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $attributes = $__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__attributesOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1)): ?>
<?php $component = $__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1; ?>
<?php unset($__componentOriginal5b1ac8d34e19cb09f7e0b3fea691ade1); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/textarea.blade.php ENDPATH**/ ?>