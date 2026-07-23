<?php
    $outputs = $tabData['outputs'] ?? null;
    $completion = $tabData['completion'] ?? ['eligible' => false, 'blockers' => []];
    $header = $header ?? [];
?>

<div class="job-360-outputs-workspace">
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <div class="xl:col-span-8 space-y-4">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('production.outputs.post')): ?>
                <?php echo $__env->make('admin.production.job-cards.workspace.partials.finished-goods-post-form', [
                    'jobCard' => $jobCard,
                    'completion' => $completion,
                    'finishedItems' => $tabData['finished_items'] ?? collect(),
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php else: ?>
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
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Finished goods output')); ?></h3>
                    <p class="mt-1 text-sm text-slate-600"><?php echo e(__('You do not have permission to post finished goods.')); ?></p>
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
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Output history')); ?></h3>
                </div>

                <?php if (isset($component)) { $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.data-table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.data-table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                     <?php $__env->slot('head', null, []); ?> 
                        <tr>
                            <th><?php echo e(__('Finished item')); ?></th>
                            <th><?php echo e(__('Quantity')); ?></th>
                            <th><?php echo e(__('Rejected')); ?></th>
                            <th><?php echo e(__('Warehouse')); ?></th>
                            <th><?php echo e(__('Journal')); ?></th>
                            <th><?php echo e(__('Posted by')); ?></th>
                            <th><?php echo e(__('Posted time')); ?></th>
                            <th><?php echo e(__('Status')); ?></th>
                        </tr>
                     <?php $__env->endSlot(); ?>
                     <?php $__env->slot('body', null, []); ?> 
                        <?php $__empty_1 = true; $__currentLoopData = $outputs ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $output): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <span class="font-mono text-xs text-slate-500"><?php echo e($output->finishedItem?->sku); ?></span><br>
                                    <?php echo e($output->finishedItem?->item_name); ?>

                                </td>
                                <td class="tabular-nums"><?php echo e(number_format((float) $output->quantity_completed, 3)); ?></td>
                                <td class="tabular-nums"><?php echo e(number_format((float) ($output->quantity_rejected ?? 0), 3)); ?></td>
                                <td><?php echo e($output->finishedWarehouse?->name ?? __('Finished goods')); ?></td>
                                <td class="font-mono text-xs"><?php echo e($output->postedJournal?->reference ?? '—'); ?></td>
                                <td><?php echo e($output->completedByUser?->name ?? '—'); ?></td>
                                <td class="whitespace-nowrap"><?php echo e($output->completed_at?->format('Y-m-d H:i') ?? '—'); ?></td>
                                <td><span class="erp-badge"><?php echo e($output->completion_status->label()); ?></span></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="8"><?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['icon' => 'cube','title' => __('No production outputs yet'),'description' => __('Finished goods will appear after production completion is posted.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'cube','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No production outputs yet')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Finished goods will appear after production completion is posted.'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $attributes = $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__attributesOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521)): ?>
<?php $component = $__componentOriginal99089f8e2ef4184d7d35db81d60c6521; ?>
<?php unset($__componentOriginal99089f8e2ef4184d7d35db81d60c6521); ?>
<?php endif; ?></td></tr>
                        <?php endif; ?>
                     <?php $__env->endSlot(); ?>
                    <?php if($outputs instanceof \Illuminate\Contracts\Pagination\Paginator): ?>
                         <?php $__env->slot('footer', null, []); ?> <?php if (isset($component)) { $__componentOriginal26f2fa5d0c1830da52192272c1d5b300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-pagination','data' => ['paginator' => $outputs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($outputs)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $attributes = $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $component = $__componentOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?> <?php $__env->endSlot(); ?>
                    <?php endif; ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0)): ?>
<?php $attributes = $__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0; ?>
<?php unset($__attributesOriginal8a75a2be9d4747e9fac92a4568c3c2d0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0)): ?>
<?php $component = $__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0; ?>
<?php unset($__componentOriginal8a75a2be9d4747e9fac92a4568c3c2d0); ?>
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
        </div>

        <aside class="xl:col-span-4">
            <?php echo $__env->make('admin.production.job-cards.workspace.partials.finished-goods-readiness-panel', [
                'jobCard' => $jobCard,
                'completion' => $completion,
                'readinessChecklist' => $tabData['readiness_checklist'] ?? [],
                'hasPostedOutput' => $tabData['has_posted_output'] ?? false,
                'header' => $header,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </aside>
    </div>
</div>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('production.outputs.post')): ?>
    <?php echo $__env->make('admin.production.job-cards.workspace.partials.complete-finished-goods-modal', [
        'jobCard' => $jobCard,
        'completion' => $completion,
        'finishedItems' => $tabData['finished_items'] ?? collect(),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/tabs/outputs.blade.php ENDPATH**/ ?>