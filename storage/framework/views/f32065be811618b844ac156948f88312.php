<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['groupedModules', 'uncatalogued' => [], 'editable' => false]));

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

foreach (array_filter((['groupedModules', 'uncatalogued' => [], 'editable' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php $__currentLoopData = $groupedModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
        <h3 class="text-base font-semibold text-erp-primary"><?php echo e(__($module['module_label'])); ?></h3>

        <div class="mt-4 space-y-6">
            <?php $__currentLoopData = $module['entities']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div>
                    <h4 class="text-sm font-medium text-slate-700"><?php echo e(__($entity['entity_label'])); ?></h4>
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                        <?php $__currentLoopData = $entity['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($editable): ?>
                                <label class="inline-flex items-center gap-2 rounded-lg border border-erp-border bg-erp-page/40 px-3 py-2 text-sm">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="<?php echo e($item['permission']); ?>"
                                        class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                        <?php if($item['checked']): echo 'checked'; endif; ?>
                                    >
                                    <span><?php echo e(__($item['label'])); ?></span>
                                </label>
                            <?php else: ?>
                                <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'rounded-lg border px-3 py-2 text-sm',
                                    'border-emerald-200 bg-emerald-50 text-emerald-800' => $item['checked'],
                                    'border-slate-200 bg-slate-50 text-slate-400' => ! $item['checked'],
                                ]); ?>">
                                    <?php echo e(__($item['label'])); ?>

                                </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <?php if(! empty($entity['extra'])): ?>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <?php $__currentLoopData = $entity['extra']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $extra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($editable): ?>
                                    <label class="inline-flex items-center gap-2 rounded-lg border border-erp-border bg-white px-3 py-2 text-sm">
                                        <input type="checkbox" name="permissions[]" value="<?php echo e($extra['permission']); ?>" class="rounded border-erp-border text-erp-accent" <?php if($extra['checked']): echo 'checked'; endif; ?>>
                                        <span><?php echo e(__($extra['label'])); ?></span>
                                    </label>
                                <?php else: ?>
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset',
                                        'bg-emerald-50 text-emerald-700 ring-emerald-600/20' => $extra['checked'],
                                        'bg-slate-100 text-slate-500 ring-slate-500/10' => ! $extra['checked'],
                                    ]); ?>">
                                        <?php echo e(__($extra['label'])); ?>

                                    </span>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($editable && $uncatalogued !== []): ?>
    <div class="rounded-lg border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        <p class="font-medium"><?php echo e(__('Additional system permissions preserved')); ?></p>
        <p class="mt-1 text-xs"><?php echo e(__('These remain assigned and are not shown in the business module groups above.')); ?></p>
        <ul class="mt-2 space-y-1 font-mono text-xs">
            <?php $__currentLoopData = $uncatalogued; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <input type="hidden" name="permissions[]" value="<?php echo e($permission); ?>">
                    <?php echo e($permission); ?>

                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\access-control\partials\grouped-permissions.blade.php ENDPATH**/ ?>