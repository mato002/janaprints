
<?php
    $columnCount = count(config('permission_catalog.columns', []));
    $editable = $editable ?? false;
    $uncatalogued = $uncatalogued ?? [];
?>

<?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-6 !p-0 overflow-hidden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6 !p-0 overflow-hidden']); ?>
        <div class="border-b border-erp-border bg-erp-page/30 px-5 py-3 sm:px-6">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__($section['module_label'])); ?></h3>
        </div>
        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid min-w-full">
                <thead>
                    <tr>
                        <th class="w-[14rem] pl-5 sm:pl-6"><?php echo e(__('Capability')); ?></th>
                        <?php $__currentLoopData = config('permission_catalog.columns', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="w-24 text-center"><?php echo e(__($meta['label'])); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-erp-border bg-white">
                    <?php $__currentLoopData = $section['rows']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-slate-50/50">
                            <td class="py-3 pl-5 font-medium text-slate-700 sm:pl-6"><?php echo e(__($row['entity_label'])); ?></td>
                            <?php $__currentLoopData = config('permission_catalog.columns', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td class="py-3 text-center">
                                    <?php if(! empty($row['cells'][$column]['permission'])): ?>
                                        <?php if($editable): ?>
                                            <label class="inline-flex cursor-pointer items-center justify-center">
                                                <input
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="<?php echo e($row['cells'][$column]['permission']); ?>"
                                                    class="h-4 w-4 rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                                    <?php if($matrixState[$row['cells'][$column]['permission']] ?? false): echo 'checked'; endif; ?>
                                                >
                                                <span class="sr-only"><?php echo e(__($row['entity_label'])); ?> — <?php echo e(__($meta['label'])); ?></span>
                                            </label>
                                        <?php elseif($matrixState[$row['cells'][$column]['permission']] ?? false): ?>
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-50 text-sm font-semibold text-emerald-700" title="<?php echo e(__('Granted')); ?>">✓</span>
                                        <?php else: ?>
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-sm text-slate-400" title="<?php echo e(__('Not granted')); ?>">✗</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-slate-300">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                        <?php if(! empty($row['extra'])): ?>
                            <tr class="bg-erp-page/40">
                                <td class="py-2.5 pl-5 text-xs font-medium uppercase tracking-wide text-slate-500 sm:pl-6"><?php echo e(__('Additional actions')); ?></td>
                                <td colspan="<?php echo e($columnCount); ?>" class="py-2.5 pr-5 sm:pr-6">
                                    <div class="flex flex-wrap gap-2">
                                        <?php $__currentLoopData = $row['extra']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $extra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php if($editable): ?>
                                                <label class="inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm <?php echo e(($matrixState[$extra['permission']] ?? false) ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-erp-border bg-white text-slate-700'); ?>">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="<?php echo e($extra['permission']); ?>"
                                                        class="rounded border-erp-border text-erp-accent focus:ring-erp-accent"
                                                        <?php if($matrixState[$extra['permission']] ?? false): echo 'checked'; endif; ?>
                                                    >
                                                    <span><?php echo e(__($extra['label'])); ?></span>
                                                </label>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset <?php echo e(($matrixState[$extra['permission']] ?? false) ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-slate-100 text-slate-500 ring-slate-500/10'); ?>">
                                                    <?php echo e(($matrixState[$extra['permission']] ?? false) ? '✓' : '✗'); ?> <?php echo e(__($extra['label'])); ?>

                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
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
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if($editable && $uncatalogued !== []): ?>
    <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50 px-5 py-4 text-sm text-amber-900">
        <p class="font-medium"><?php echo e(__('Additional system permissions preserved')); ?></p>
        <p class="mt-1 text-xs text-amber-800"><?php echo e(__('These remain assigned and are not mapped in the matrix above.')); ?></p>
        <div class="mt-3 flex flex-wrap gap-2">
            <?php $__currentLoopData = $uncatalogued; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="inline-flex items-center gap-2 rounded-lg border border-amber-200 bg-white px-2 py-1 font-mono text-xs">
                    <input type="hidden" name="permissions[]" value="<?php echo e($permission); ?>">
                    <?php echo e($permission); ?>

                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\access-control\partials\matrix-table.blade.php ENDPATH**/ ?>