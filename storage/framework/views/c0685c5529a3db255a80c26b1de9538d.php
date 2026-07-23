<?php
    $progress = $tabData['progress'] ?? [];
    $steps = $progress['all'] ?? collect();
    $summary = $tabData['summary'] ?? [];
    $outsourceCtx = $tabData['outsource'] ?? [];
    $isAtVendor = $jobCard->status === App\Enums\ProductionJobCardStatus::Outsourced;
?>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Production route')); ?></h2>
        <?php if(($summary['total'] ?? 0) > 0): ?>
            <p class="mt-1 text-sm text-slate-600">
                <?php echo e(__(':done of :total steps complete', ['done' => $summary['completed'] ?? 0, 'total' => $summary['total'] ?? 0])); ?>

                <?php if(! empty($summary['current'])): ?>
                    <span class="text-slate-400">·</span>
                    <?php echo e(__('Current')); ?>: <span class="font-medium"><?php echo e($summary['current']); ?></span>
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
    <?php if(($summary['total'] ?? 0) > 0): ?>
        <div class="flex items-center gap-3 text-sm">
            <div class="w-32 overflow-hidden rounded-full bg-slate-200">
                <div class="h-2 rounded-full bg-erp-accent" style="width: <?php echo e($summary['percent'] ?? 0); ?>%"></div>
            </div>
            <span class="tabular-nums font-medium text-slate-700"><?php echo e($summary['percent'] ?? 0); ?>%</span>
        </div>
    <?php endif; ?>
</div>

<?php if($steps->isNotEmpty()): ?>
    <div class="erp-card overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th class="w-12">#</th>
                    <th><?php echo e(__('Step')); ?></th>
                    <th><?php echo e(__('Work center')); ?></th>
                    <th><?php echo e(__('Status')); ?></th>
                    <th><?php echo e(__('Started')); ?></th>
                    <th><?php echo e(__('Completed')); ?></th>
                    <th><?php echo e(__('Outsource')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="<?php echo e(($progress['current'] ?? null)?->id === $step->id ? 'bg-erp-accent/5' : ''); ?>">
                        <td class="tabular-nums text-slate-500"><?php echo e($step->sequence); ?></td>
                        <td class="font-medium text-slate-900"><?php echo e($step->step_name); ?></td>
                        <td class="text-slate-600"><?php echo e($step->workCenter?->name ?? '—'); ?></td>
                        <td>
                            <?php if(($tabData['can_update'] ?? false) && ! in_array($step->status->value, ['completed', 'skipped'], true)): ?>
                                <form method="POST" action="<?php echo e(route('admin.production.job-cards.route-steps.update', [$jobCard, $step])); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('PUT'); ?>
                                    <select name="status" class="erp-select w-full min-w-[8rem] text-xs py-1" onchange="this.form.submit()">
                                        <?php $__currentLoopData = $tabData['statuses'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($status->value); ?>" <?php if($step->status === $status): echo 'selected'; endif; ?>><?php echo e($status->label()); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </form>
                            <?php else: ?>
                                <span class="erp-badge <?php echo e($step->status->badgeClass()); ?>"><?php echo e($step->status->label()); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="whitespace-nowrap text-xs text-slate-600">
                            <?php echo e($step->started_at?->format('Y-m-d H:i') ?? '—'); ?>

                        </td>
                        <td class="whitespace-nowrap text-xs text-slate-600">
                            <?php if($step->completed_at): ?>
                                <?php echo e($step->completed_at->format('Y-m-d H:i')); ?>

                                <?php if($step->completedByUser): ?>
                                    <span class="block text-[11px] text-slate-500"><?php echo e($step->completedByUser->name); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td class="text-xs">
                            <?php if($isAtVendor): ?>
                                <span class="rounded bg-violet-100 px-1.5 py-0.5 font-medium text-violet-800"><?php echo e(__('At vendor')); ?></span>
                                <?php if($outsourceCtx['vendor'] ?? null): ?>
                                    <span class="mt-0.5 block text-slate-500"><?php echo e($outsourceCtx['vendor']->vendor_name); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
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
        <?php if (isset($component)) { $__componentOriginal99089f8e2ef4184d7d35db81d60c6521 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal99089f8e2ef4184d7d35db81d60c6521 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.empty-state','data' => ['title' => __('No production route defined'),'description' => __('Route steps are copied from the product catalog when the job card is created. Edit the product route template or re-create the job from a configured catalogue item.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('No production route defined')),'description' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Route steps are copied from the product catalog when the job card is created. Edit the product route template or re-create the job from a configured catalogue item.'))]); ?>
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

<?php if(($outsourceCtx['can_outsource'] ?? false) || ($outsourceCtx['can_return'] ?? false) || ($outsourceCtx['vendor'] ?? null)): ?>
    <?php echo $__env->make('admin.production.job-cards.workspace.partials.outsource', [
        'jobCard' => $jobCard,
        'tabData' => $tabData,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\tabs\route.blade.php ENDPATH**/ ?>