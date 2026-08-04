<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'label',
    'usedLabel',
    'freeLabel',
    'percent',
    'status' => null,
    'uploadsLabel' => null,
    'backupLabel' => null,
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
    'usedLabel',
    'freeLabel',
    'percent',
    'status' => null,
    'uploadsLabel' => null,
    'backupLabel' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $pct = min(100, max(0, (float) $percent));

    $variant = match (true) {
        $pct >= 80 => 'danger',
        $pct >= 60 => 'warning',
        default => 'success',
    };

    $barClass = match ($variant) {
        'danger' => 'exec-progress__bar--danger',
        'warning' => 'exec-progress__bar--warning',
        default => 'exec-progress__bar--success',
    };
?>

<div <?php echo e($attributes->merge(['class' => 'health-progress-card rounded-lg border border-erp-border bg-erp-card p-4 shadow-card'])); ?>>
    <?php if (isset($component)) { $__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-section-header','data' => ['title' => $label,'status' => $status ?? $variant]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-section-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($status ?? $variant)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc)): ?>
<?php $attributes = $__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc; ?>
<?php unset($__attributesOriginal69aa9ebf9e46f2dd640de69819b8ffdc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc)): ?>
<?php $component = $__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc; ?>
<?php unset($__componentOriginal69aa9ebf9e46f2dd640de69819b8ffdc); ?>
<?php endif; ?>

    <div class="mt-4">
        <div class="mb-2 flex items-end justify-between gap-2">
            <span class="text-2xl font-bold tabular-nums text-erp-primary"><?php echo e($usedLabel); ?></span>
            <span class="text-sm font-semibold tabular-nums text-slate-600"><?php echo e($pct); ?>% <?php echo e(__('Used')); ?></span>
        </div>

        <div class="exec-progress__track" role="progressbar" aria-valuenow="<?php echo e($pct); ?>" aria-valuemin="0" aria-valuemax="100">
            <div class="exec-progress__bar <?php echo e($barClass); ?>" style="width: <?php echo e($pct); ?>%"></div>
        </div>

        <p class="mt-2 text-sm text-slate-600">
            <span class="font-medium text-erp-primary"><?php echo e($freeLabel); ?></span> <?php echo e(__('free')); ?>

        </p>
    </div>

    <?php if($uploadsLabel || $backupLabel): ?>
        <div class="mt-4 grid gap-2 border-t border-erp-border pt-3 sm:grid-cols-2">
            <?php if($uploadsLabel): ?>
                <div class="rounded-md bg-erp-page/60 px-3 py-2">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Uploads')); ?></p>
                    <p class="mt-0.5 text-sm font-semibold text-erp-primary"><?php echo e($uploadsLabel); ?></p>
                </div>
            <?php endif; ?>
            <?php if($backupLabel): ?>
                <div class="rounded-md bg-erp-page/60 px-3 py-2">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Backups')); ?></p>
                    <p class="mt-0.5 text-sm font-semibold text-erp-primary"><?php echo e($backupLabel); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\health\health-progress-card.blade.php ENDPATH**/ ?>