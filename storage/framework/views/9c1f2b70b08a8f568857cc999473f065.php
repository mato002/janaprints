<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'items' => [],
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
    'items' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'health-summary-strip grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6'])); ?> role="list" aria-label="<?php echo e(__('System health summary')); ?>">
    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if (isset($component)) { $__componentOriginal299f2dc3025ad59be872d03e33040b63 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal299f2dc3025ad59be872d03e33040b63 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-status-card','data' => ['role' => 'listitem','label' => $item['label'],'status' => $item['status'],'value' => $item['value'],'detail' => $item['detail'] ?? null,'icon' => $item['icon'] ?? null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-status-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['role' => 'listitem','label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['label']),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['status']),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['value']),'detail' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['detail'] ?? null),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($item['icon'] ?? null)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal299f2dc3025ad59be872d03e33040b63)): ?>
<?php $attributes = $__attributesOriginal299f2dc3025ad59be872d03e33040b63; ?>
<?php unset($__attributesOriginal299f2dc3025ad59be872d03e33040b63); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal299f2dc3025ad59be872d03e33040b63)): ?>
<?php $component = $__componentOriginal299f2dc3025ad59be872d03e33040b63; ?>
<?php unset($__componentOriginal299f2dc3025ad59be872d03e33040b63); ?>
<?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\health\health-summary-strip.blade.php ENDPATH**/ ?>