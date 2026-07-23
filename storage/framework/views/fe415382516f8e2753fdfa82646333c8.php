<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'erp-confirm',
    'title' => __('Confirm action'),
    'message' => '',
    'confirmLabel' => __('Confirm'),
    'cancelLabel' => __('Cancel'),
    'variant' => 'danger',
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
    'name' => 'erp-confirm',
    'title' => __('Confirm action'),
    'message' => '',
    'confirmLabel' => __('Confirm'),
    'cancelLabel' => __('Cancel'),
    'variant' => 'danger',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $confirmClass = $variant === 'danger' ? 'erp-btn-danger' : 'erp-btn-primary';
?>

<?php if (isset($component)) { $__componentOriginal9f64f32e90b9102968f2bc548315018c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9f64f32e90b9102968f2bc548315018c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal','data' => ['name' => $name,'focusable' => true,'maxWidth' => 'md']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($name),'focusable' => true,'maxWidth' => 'md']); ?>
    <div class="p-6">
        <h2 class="text-lg font-semibold text-erp-primary"><?php echo e($title); ?></h2>
        <?php if($message !== ''): ?>
            <p class="mt-2 text-sm text-slate-600"><?php echo e($message); ?></p>
        <?php endif; ?>
        <?php if(isset($body)): ?>
            <div class="mt-3 text-sm text-slate-600"><?php echo e($body); ?></div>
        <?php endif; ?>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" class="erp-btn-secondary" x-on:click="$dispatch('close-modal', '<?php echo e($name); ?>')">
                <?php echo e($cancelLabel); ?>

            </button>
            <?php if(isset($confirm)): ?>
                <?php echo e($confirm); ?>

            <?php else: ?>
                <button type="button" class="<?php echo \Illuminate\Support\Arr::toCssClasses([$confirmClass]); ?>" data-erp-confirm-action>
                    <?php echo e($confirmLabel); ?>

                </button>
            <?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $attributes = $__attributesOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__attributesOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9f64f32e90b9102968f2bc548315018c)): ?>
<?php $component = $__componentOriginal9f64f32e90b9102968f2bc548315018c; ?>
<?php unset($__componentOriginal9f64f32e90b9102968f2bc548315018c); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\confirm-dialog.blade.php ENDPATH**/ ?>