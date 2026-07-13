<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'moduleTitle',
    'moduleKey' => null,
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
    'moduleTitle',
    'moduleKey' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginal5a0dd1e42bf41dfc1461786d345c03da = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5a0dd1e42bf41dfc1461786d345c03da = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.module-workspace-search','data' => ['moduleTitle' => $moduleTitle,'moduleKey' => $moduleKey,'attributes' => $attributes]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.module-workspace-search'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['module-title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($moduleTitle),'module-key' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($moduleKey),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5a0dd1e42bf41dfc1461786d345c03da)): ?>
<?php $attributes = $__attributesOriginal5a0dd1e42bf41dfc1461786d345c03da; ?>
<?php unset($__attributesOriginal5a0dd1e42bf41dfc1461786d345c03da); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5a0dd1e42bf41dfc1461786d345c03da)): ?>
<?php $component = $__componentOriginal5a0dd1e42bf41dfc1461786d345c03da; ?>
<?php unset($__componentOriginal5a0dd1e42bf41dfc1461786d345c03da); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/workspace-search-bar.blade.php ENDPATH**/ ?>