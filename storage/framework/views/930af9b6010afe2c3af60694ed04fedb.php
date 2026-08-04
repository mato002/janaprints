<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['can_export', 'filters', 'schedule_frequencies' => null]));

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

foreach (array_filter((['can_export', 'filters', 'schedule_frequencies' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex flex-wrap items-center gap-2">
    <?php if (isset($component)) { $__componentOriginalf419e868e892b32e6daa894c958d94bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf419e868e892b32e6daa894c958d94bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.export-dropdown','data' => ['postAction' => route('admin.reports.production.export', $filters),'postFields' => $filters,'canExport' => $can_export]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.export-dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['post-action' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('admin.reports.production.export', $filters)),'post-fields' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($filters),'can-export' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($can_export)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $attributes = $__attributesOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__attributesOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf419e868e892b32e6daa894c958d94bc)): ?>
<?php $component = $__componentOriginalf419e868e892b32e6daa894c958d94bc; ?>
<?php unset($__componentOriginalf419e868e892b32e6daa894c958d94bc); ?>
<?php endif; ?>

    <?php if($can_export && $schedule_frequencies): ?>
        <details class="relative">
            <summary class="erp-btn-secondary cursor-pointer text-xs list-none"><?php echo e(__('Schedule Export')); ?></summary>
            <div class="absolute right-0 z-10 mt-2 w-64 rounded-lg border border-erp-border bg-white p-3 shadow-lg">
                <form method="POST" action="<?php echo e(route('admin.reports.production.export', $filters)); ?>" class="space-y-3" data-turbo="false" target="_top">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="schedule" value="1">
                    <div>
                        <label class="text-[11px] text-slate-500" for="schedule_format"><?php echo e(__('Format')); ?></label>
                        <select id="schedule_format" name="format" class="erp-input mt-1 w-full">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] text-slate-500" for="schedule_frequency"><?php echo e(__('Frequency')); ?></label>
                        <select id="schedule_frequency" name="frequency" class="erp-input mt-1 w-full">
                            <?php $__currentLoopData = $schedule_frequencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>"><?php echo e(__($label)); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <button type="submit" class="erp-btn-primary w-full text-xs"><?php echo e(__('Save Schedule')); ?></button>
                </form>
            </div>
        </details>

        <a href="<?php echo e(route('admin.reports.production.print', $filters)); ?>" target="_blank" class="erp-btn-secondary text-xs">
            <?php echo e(__('Print')); ?>

        </a>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\reports\production\partials\export-actions.blade.php ENDPATH**/ ?>