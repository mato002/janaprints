<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'alert',
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
    'alert',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $severity = $alert['severity'];
    $severityKey = is_object($severity) && enum_exists($severity::class) ? $severity->value : (string) $severity;

    $border = match ($severityKey) {
        'critical' => 'border-l-red-500 bg-red-50/30',
        'warning' => 'border-l-amber-500 bg-amber-50/30',
        default => 'border-l-slate-400 bg-slate-50/40',
    };

    $icon = match ($severityKey) {
        'critical' => 'x-circle',
        'warning' => 'exclamation',
        default => 'information-circle',
    };

    $meta = match ($alert['type'] ?? '') {
        'backup' => [
            'impact' => __('Disaster recovery may be unavailable.'),
            'action' => __('Configure backup schedules and verify retention.'),
        ],
        'database' => [
            'impact' => __('ERP data access and transactions may be affected.'),
            'action' => __('Verify database connectivity and apply pending migrations.'),
        ],
        'storage' => [
            'impact' => __('Uploads, logs, and backups may fail when disk is full.'),
            'action' => __('Free disk space or expand storage capacity.'),
        ],
        'queue' => [
            'impact' => __('Background jobs and notifications may be delayed.'),
            'action' => __('Review failed jobs and ensure queue workers are running.'),
        ],
        default => [
            'impact' => __('Operational risk detected in monitored infrastructure.'),
            'action' => __('Review the alert details and take corrective action.'),
        ],
    };
?>

<article <?php echo e($attributes->merge(['class' => "health-alert-card rounded-lg border border-erp-border border-l-4 p-4 shadow-card {$border}"])); ?>>
    <div class="flex flex-wrap items-start gap-3">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white shadow-sm">
            <?php if (isset($component)) { $__componentOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal906aaa6a63a2f5f8b29c23c3195c96dd = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.icon','data' => ['name' => $icon,'class' => 'h-5 w-5 text-erp-primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'class' => 'h-5 w-5 text-erp-primary']); ?>
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
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-bold uppercase tracking-wide text-erp-primary"><?php echo e($alert['title']); ?></h3>
                <?php if (isset($component)) { $__componentOriginal16682510d2d606e0990dc24bb6455e92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal16682510d2d606e0990dc24bb6455e92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.health.health-status-badge','data' => ['status' => $severityKey,'label' => $severityKey === 'critical' ? __('Critical') : ($severityKey === 'warning' ? __('Warning') : __('Info'))]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.health.health-status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($severityKey),'label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($severityKey === 'critical' ? __('Critical') : ($severityKey === 'warning' ? __('Warning') : __('Info')))]); ?>
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
            <p class="mt-2 text-sm text-slate-700"><?php echo e($alert['message']); ?></p>
            <dl class="mt-3 grid gap-2 text-xs sm:grid-cols-2">
                <div class="rounded-md bg-white/70 px-3 py-2">
                    <dt class="font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Impact')); ?></dt>
                    <dd class="mt-0.5 text-slate-700"><?php echo e($meta['impact']); ?></dd>
                </div>
                <div class="rounded-md bg-white/70 px-3 py-2">
                    <dt class="font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Recommended Action')); ?></dt>
                    <dd class="mt-0.5 text-slate-700"><?php echo e($meta['action']); ?></dd>
                </div>
            </dl>
        </div>
    </div>
</article>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\components\admin\health\health-alert-card.blade.php ENDPATH**/ ?>