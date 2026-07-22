<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name',
    'label' => null,
    'required' => false,
    'visible' => true,
    'readonly' => false,
    'help' => null,
    'placeholder' => null,
    'colSpan' => 1,
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
    'required' => false,
    'visible' => true,
    'readonly' => false,
    'help' => null,
    'placeholder' => null,
    'colSpan' => 1,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if($visible): ?>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'erp-form-field',
        'md:col-span-2' => (int) $colSpan === 2,
    ]); ?>">
        <?php if($label): ?>
            <?php if (isset($component)) { $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input-label','data' => ['for' => $name,'value' => $label,'required' => $required]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('input-label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'required' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($required)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $attributes = $__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__attributesOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581)): ?>
<?php $component = $__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581; ?>
<?php unset($__componentOriginale3da9d84bb64e4bc2eeebaafabfb2581); ?>
<?php endif; ?>
        <?php endif; ?>

        <?php if($help): ?>
            <p class="mt-0.5 text-xs text-erp-muted"><?php echo e($help); ?></p>
        <?php endif; ?>

        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mt-1' => $label !== null]); ?>">
            <?php echo e($slot); ?>

        </div>

        <?php if (isset($component)) { $__componentOriginalea9570ffb6e438fad0d70d52a821a8a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.field-error','data' => ['name' => $name]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.field-error'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9)): ?>
<?php $attributes = $__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9; ?>
<?php unset($__attributesOriginalea9570ffb6e438fad0d70d52a821a8a9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalea9570ffb6e438fad0d70d52a821a8a9)): ?>
<?php $component = $__componentOriginalea9570ffb6e438fad0d70d52a821a8a9; ?>
<?php unset($__componentOriginalea9570ffb6e438fad0d70d52a821a8a9); ?>
<?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/form-field.blade.php ENDPATH**/ ?>