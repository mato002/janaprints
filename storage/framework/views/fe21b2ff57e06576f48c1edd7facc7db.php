<?php
    $requirements = $tabData['requirements'] ?? collect();
    $costs = $tabData['costs'] ?? [];
?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Material cost summary')); ?></h3>
    <dl class="grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
        <div><dt class="text-slate-500"><?php echo e(__('Estimated')); ?></dt><dd class="font-medium tabular-nums"><?php echo e(number_format((float) ($costs['estimated_material_cost'] ?? 0), 2)); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Issued')); ?></dt><dd class="font-medium tabular-nums"><?php echo e(number_format((float) ($costs['issued_material_cost'] ?? 0), 2)); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Consumed')); ?></dt><dd class="font-medium tabular-nums"><?php echo e(number_format((float) ($costs['consumed_material_cost'] ?? 0), 2)); ?></dd></div>
        <div><dt class="text-slate-500"><?php echo e(__('Waste')); ?></dt><dd class="font-medium tabular-nums"><?php echo e(number_format((float) ($costs['waste_cost'] ?? 0), 2)); ?></dd></div>
    </dl>
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

<?php if(($tabData['can_generate'] ?? false) && ! ($tabData['has_requirements'] ?? false)): ?>
    <?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.materials.generate', $jobCard)); ?>" class="flex flex-wrap items-end gap-2">
            <?php echo csrf_field(); ?>
            <div class="min-w-[12rem]">
                <label class="erp-label text-xs"><?php echo e(__('Warehouse')); ?></label>
                <select name="warehouse_id" class="erp-input w-full text-sm" required>
                    <?php $__currentLoopData = $tabData['warehouses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wh): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($wh->id); ?>"><?php echo e($wh->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Generate requirements')); ?></button>
        </form>
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
<?php endif; ?>

<?php if($tabData['can_reserve'] ?? false): ?>
    <form method="POST" action="<?php echo e(route('admin.production.job-cards.materials.reserve-all', $jobCard)); ?>" class="mb-4">
        <?php echo csrf_field(); ?>
        <button type="submit" class="erp-btn-secondary text-sm"><?php echo e(__('Reserve all available')); ?></button>
    </form>
<?php endif; ?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Material requirements')); ?></h3>
    <?php if($requirements->isNotEmpty()): ?>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Material')); ?></th>
                        <th><?php echo e(__('Required')); ?></th>
                        <th><?php echo e(__('Available')); ?></th>
                        <th><?php echo e(__('Shortfall')); ?></th>
                        <th><?php echo e(__('Issued')); ?></th>
                        <th><?php echo e(__('Consumed')); ?></th>
                        <th><?php echo e(__('Waste')); ?></th>
                        <th><?php echo e(__('Returned')); ?></th>
                        <th><?php echo e(__('Status')); ?></th>
                        <?php if($tabData['can_consume'] ?? false): ?>
                            <th></th>
                        <?php endif; ?>
                        <?php if($tabData['can_reserve'] ?? false): ?>
                            <th></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($row['item_name']); ?> <span class="text-slate-500">(<?php echo e($row['sku']); ?>)</span></td>
                            <td class="tabular-nums"><?php echo e($row['required']); ?> <?php echo e($row['unit']); ?></td>
                            <td class="tabular-nums"><?php echo e($row['available']); ?></td>
                            <td class="tabular-nums <?php echo e($row['shortfall'] > 0 ? 'text-red-600 font-medium' : ''); ?>"><?php echo e($row['shortfall']); ?></td>
                            <td class="tabular-nums"><?php echo e($row['issued']); ?></td>
                            <td class="tabular-nums"><?php echo e($row['consumed']); ?></td>
                            <td class="tabular-nums"><?php echo e($row['waste']); ?></td>
                            <td class="tabular-nums"><?php echo e($row['returned']); ?></td>
                            <td><span class="erp-badge text-xs"><?php echo e($row['status']->label()); ?></span></td>
                            <?php if($tabData['can_consume'] ?? false): ?>
                                <td class="whitespace-nowrap">
                                    <?php if(($row['remaining'] ?? 0) > 0): ?>
                                        <form method="POST" action="<?php echo e(route('admin.production.job-cards.materials.consume', [$jobCard, $row['requirement']])); ?>" class="inline-flex items-center gap-1">
                                            <?php echo csrf_field(); ?>
                                            <input type="number" step="0.001" min="0.001" name="quantity" class="erp-input w-20 text-xs" value="<?php echo e($row['remaining']); ?>">
                                            <button type="submit" class="erp-btn-primary text-xs"><?php echo e(__('Consume')); ?></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <?php if($tabData['can_reserve'] ?? false): ?>
                                <td class="whitespace-nowrap">
                                    <?php if($row['can_reserve'] ?? false): ?>
                                        <form method="POST" action="<?php echo e(route('admin.production.job-cards.materials.reserve', [$jobCard, $row['requirement']])); ?>" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Reserve')); ?></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['title' => __('No material requirements'),'description' => __('Requirements are snapshotted from the product catalog BOM when the job card is created, or can be generated manually.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No material requirements')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Requirements are snapshotted from the product catalog BOM when the job card is created, or can be generated manually.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
    <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\materials.blade.php ENDPATH**/ ?>