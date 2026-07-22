<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['readiness', 'report_ready', 'context' => __('sales reports')]));

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

foreach (array_filter((['readiness', 'report_ready', 'context' => __('sales reports')]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6']); ?>
    <div class="mb-3 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary"><?php echo e(__('Data Readiness')); ?></h2>
            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('Operational sources required before :context can run.', ['context' => $context])); ?></p>
        </div>
        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
            'bg-emerald-50 text-emerald-700' => $report_ready,
            'bg-amber-50 text-amber-700' => ! $report_ready,
        ]); ?>">
            <?php echo e($report_ready ? __('Ready') : __('Not Ready')); ?>

        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                    <th class="px-3 py-2 font-semibold"><?php echo e(__('Source')); ?></th>
                    <th class="px-3 py-2 font-semibold"><?php echo e(__('Table')); ?></th>
                    <th class="px-3 py-2 font-semibold"><?php echo e(__('Status')); ?></th>
                    <th class="px-3 py-2 font-semibold"><?php echo e(__('Notes')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $readiness; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-b border-erp-border/60">
                        <td class="px-3 py-2 font-medium text-erp-primary"><?php echo e($row['source']); ?></td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-600"><?php echo e($row['table']); ?></td>
                        <td class="px-3 py-2">
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'inline-flex rounded-full px-2 py-0.5 text-xs font-semibold',
                                'bg-emerald-50 text-emerald-700' => $row['ready'],
                                'bg-rose-50 text-rose-700' => ! $row['ready'] && ! ($row['optional'] ?? false),
                                'bg-slate-100 text-slate-600' => ! $row['ready'] && ($row['optional'] ?? false),
                            ]); ?>">
                                <?php echo e($row['ready'] ? __('Ready') : __('Unavailable')); ?>

                            </span>
                        </td>
                        <td class="px-3 py-2 text-slate-600"><?php echo e($row['notes']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\commercial\reports\sales\partials\readiness-table.blade.php ENDPATH**/ ?>