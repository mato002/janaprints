<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'value',
    'hint' => null,
    'status' => null,
    'icon' => null,
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
    'label',
    'value',
    'hint' => null,
    'status' => null,
    'icon' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $statusKey = $status
        ? (is_object($status) && enum_exists($status::class) ? $status->value : (string) $status)
        : null;

    $valueTone = match ($statusKey) {
        'critical', 'danger' => 'text-red-700',
        'warning' => 'text-amber-800',
        'healthy', 'success' => 'text-emerald-800',
        default => 'text-erp-primary',
    };
?>

<div <?php echo e($attributes->merge(['class' => 'health-metric-card rounded-lg border border-erp-border bg-erp-card p-3 shadow-card transition-shadow hover:shadow-card-hover sm:p-4'])); ?>>
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($label); ?></p>
            <p class="mt-1.5 text-lg font-bold tabular-nums <?php echo e($valueTone); ?>"><?php echo e($value); ?></p>
            <?php if($hint): ?>
                <p class="mt-1 text-xs text-slate-500"><?php echo e($hint); ?></p>
            <?php endif; ?>
        </div>
        <?php if($icon): ?>
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-erp-accent/10 text-erp-accent">
                <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $icon,'class' => 'h-4 w-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'h-4 w-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $attributes = $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd)): ?>
<?php $component = $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd; ?>
<?php unset($__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd); ?>
<?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if($statusKey): ?>
        <div class="mt-2.5 border-t border-erp-border/60 pt-2">
            <?php if (isset($component)) { $__componentOriginal16682510d2d606e0990dc24bb6455e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal16682510d2d606e0990dc24bb6455e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-status-badge','data' => ['status' => $statusKey]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statusKey)]); ?>
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
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\health\health-metric-card.blade.php ENDPATH**/ ?>