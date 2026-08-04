<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'subtitle' => null,
    'status' => null,
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
    'title',
    'subtitle' => null,
    'status' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'flex flex-wrap items-start justify-between gap-3'])); ?>>
    <div class="min-w-0">
        <h2 class="text-xs font-bold uppercase tracking-wider text-erp-primary"><?php echo e($title); ?></h2>
        <?php if($subtitle): ?>
            <p class="mt-0.5 text-xs text-slate-500"><?php echo e($subtitle); ?></p>
        <?php endif; ?>
    </div>
    <?php if($status): ?>
        <?php if (isset($component)) { $__componentOriginal16682510d2d606e0990dc24bb6455e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal16682510d2d606e0990dc24bb6455e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-status-badge','data' => ['status' => $status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal16682510d2d606e0990dc24bb6455e92)): ?>
<?php $attributes = $__attributesOriginal16682510d2d606e0990dc24bb6455e92; ?>
<?php unset($__attributesOriginal16682510d2d606e0990dc24bb6455e92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal16682510d2d606e0990dc24bb6455e92)): ?>
<?php $component = $__componentOriginal16682510d2d606e0990dc24bb6455e92; ?>
<?php unset($__componentOriginal16682510d2d606e0990dc24bb6455e92); ?>
<?php endif; ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\health\health-section-header.blade.php ENDPATH**/ ?>