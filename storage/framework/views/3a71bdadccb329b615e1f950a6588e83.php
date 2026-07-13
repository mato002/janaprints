<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'url' => null,
    'frameId' => 'module-workspace-content',
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
    'url' => null,
    'frameId' => 'module-workspace-content',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginal308cd3c4087636aca146ca95b542790a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal308cd3c4087636aca146ca95b542790a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.workspace-content','data' => ['url' => $url,'frameId' => $frameId,'attributes' => $attributes->except(['url', 'frameId'])->merge(['class' => 'flex min-h-0 flex-1 flex-col overflow-hidden'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.workspace-content'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($url),'frame-id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($frameId),'attributes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($attributes->except(['url', 'frameId'])->merge(['class' => 'flex min-h-0 flex-1 flex-col overflow-hidden']))]); ?>
    <?php echo e($slot); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal308cd3c4087636aca146ca95b542790a)): ?>
<?php $attributes = $__attributesOriginal308cd3c4087636aca146ca95b542790a; ?>
<?php unset($__attributesOriginal308cd3c4087636aca146ca95b542790a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal308cd3c4087636aca146ca95b542790a)): ?>
<?php $component = $__componentOriginal308cd3c4087636aca146ca95b542790a; ?>
<?php unset($__componentOriginal308cd3c4087636aca146ca95b542790a); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/components/admin/workspace-content-shell.blade.php ENDPATH**/ ?>