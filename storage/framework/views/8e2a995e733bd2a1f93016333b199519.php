<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'status' => 'unknown',
    'value',
    'detail' => null,
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
    'status' => 'unknown',
    'value',
    'detail' => null,
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
    $statusKey = is_object($status) && enum_exists($status::class) ? $status->value : (string) $status;

    $accent = match ($statusKey) {
        'healthy', 'success' => 'border-emerald-500/80 bg-emerald-50/40',
        'warning', 'pending' => 'border-amber-500/80 bg-amber-50/40',
        'critical', 'danger' => 'border-red-500/80 bg-red-50/40',
        default => 'border-slate-300 bg-slate-50/60',
    };

    $dot = match ($statusKey) {
        'healthy', 'success' => 'bg-emerald-500',
        'warning', 'pending' => 'bg-amber-500',
        'critical', 'danger' => 'bg-red-500',
        default => 'bg-slate-400',
    };
?>

<div <?php echo e($attributes->merge(['class' => "health-status-card rounded-lg border border-erp-border border-l-4 shadow-card {$accent}"])); ?>>
    <div class="flex items-start justify-between gap-2 p-3 sm:p-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 shrink-0 rounded-full <?php echo e($dot); ?>" aria-hidden="true"></span>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-600"><?php echo e($label); ?></p>
            </div>
            <p class="mt-1.5 text-sm font-bold text-erp-primary"><?php echo e($value); ?></p>
            <?php if($detail): ?>
                <p class="mt-0.5 text-xs text-slate-500"><?php echo e($detail); ?></p>
            <?php endif; ?>
        </div>
        <?php if($icon): ?>
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/80 text-erp-accent shadow-sm">
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
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\health\health-status-card.blade.php ENDPATH**/ ?>