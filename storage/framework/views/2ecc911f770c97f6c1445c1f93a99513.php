<?php
    $issues = $tabData['issues'] ?? null;
    $requirements = $tabData['requirements'] ?? collect();
?>

<?php if($tabData['can_issue'] ?? false): ?>
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
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Issue materials')); ?></h3>
            <form method="POST" action="<?php echo e(route('admin.production.job-cards.materials.issue-all', $jobCard)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="erp-btn-primary text-sm"><?php echo e(__('Issue all remaining')); ?></button>
            </form>
        </div>
        <?php if($requirements->isNotEmpty()): ?>
            <div class="mt-3 overflow-x-auto">
                <table class="erp-table w-full text-sm">
                    <thead>
                        <tr>
                            <th><?php echo e(__('Material')); ?></th>
                            <th><?php echo e(__('Required')); ?></th>
                            <th><?php echo e(__('Issued')); ?></th>
                            <th><?php echo e(__('Remaining')); ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(($row['requirement']->remainingToIssue() ?? 0) > 0): ?>
                                <tr>
                                    <td><?php echo e($row['item_name']); ?></td>
                                    <td class="tabular-nums"><?php echo e($row['required']); ?></td>
                                    <td class="tabular-nums"><?php echo e($row['issued']); ?></td>
                                    <td class="tabular-nums"><?php echo e($row['requirement']->remainingToIssue()); ?></td>
                                    <td>
                                        <form method="POST" action="<?php echo e(route('admin.production.job-cards.materials.issue', [$jobCard, $row['requirement']])); ?>" class="inline-flex gap-1">
                                            <?php echo csrf_field(); ?>
                                            <input type="number" step="0.001" name="quantity" class="erp-input w-20 text-xs" placeholder="<?php echo e(__('Qty')); ?>">
                                            <button type="submit" class="erp-btn-ghost text-xs"><?php echo e(__('Issue')); ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
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
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Issue history')); ?></h3>
    <?php if($issues && $issues->count() > 0): ?>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th><?php echo e(__('Material')); ?></th>
                        <th><?php echo e(__('Qty')); ?></th>
                        <th><?php echo e(__('Unit cost')); ?></th>
                        <th><?php echo e(__('Warehouse')); ?></th>
                        <th><?php echo e(__('Issued by')); ?></th>
                        <th><?php echo e(__('Issued at')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($issue->inventoryItem?->item_name); ?> <span class="text-slate-500">(<?php echo e($issue->inventoryItem?->sku); ?>)</span></td>
                            <td class="tabular-nums"><?php echo e($issue->quantity); ?> <?php echo e($issue->inventoryItem?->unitOfMeasure?->code); ?></td>
                            <td class="tabular-nums"><?php echo e(number_format((float) $issue->unit_cost, 2)); ?></td>
                            <td><?php echo e($issue->warehouse?->name); ?></td>
                            <td><?php echo e($issue->issuer?->name); ?></td>
                            <td><?php echo e($issue->issued_at?->format('Y-m-d H:i')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php if($issues->hasPages()): ?>
            <div class="mt-4"><?php echo e($issues->links()); ?></div>
        <?php endif; ?>
    <?php else: ?>
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['title' => __('No issues recorded'),'description' => __('Issue materials to production before consumption.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No issues recorded')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Issue materials to production before consumption.'))]); ?>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\material-issues.blade.php ENDPATH**/ ?>