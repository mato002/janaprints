<?php ($readiness = $queueReadiness ?? []); ?>
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
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary"><?php echo e(__('Queue Readiness')); ?></h2>
            <p class="mt-1 text-sm text-slate-500">
                <?php echo e(__('Connection')); ?>: <span class="font-medium text-erp-primary"><?php echo e(strtoupper($readiness['connection'] ?? 'unknown')); ?></span>
                <?php if($readiness['healthy'] ?? false): ?>
                    · <span class="text-emerald-700"><?php echo e(__('Healthy')); ?></span>
                <?php else: ?>
                    · <span class="text-amber-700"><?php echo e(__('Attention required')); ?></span>
                <?php endif; ?>
            </p>
        </div>
        <p class="text-xs text-slate-500"><?php echo e(__('See docs/PRODUCTION_QUEUE_AND_SCHEDULER_RUNBOOK.md in the repository.')); ?></p>
    </div>

    <?php if(! empty($readiness['warnings'])): ?>
        <ul class="mt-4 space-y-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <?php $__currentLoopData = $readiness['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($warning); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Queue Backlog')); ?></h3>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                <?php $__currentLoopData = $readiness['backlog'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-lg border border-erp-border bg-erp-page px-3 py-2">
                        <p class="text-[11px] uppercase tracking-wide text-slate-500"><?php echo e($queue); ?></p>
                        <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-lg font-semibold tabular-nums', 'text-red-600' => $count >= 100, 'text-erp-primary' => $count < 100]); ?>"><?php echo e(number_format($count)); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <p class="mt-2 text-xs text-slate-500"><?php echo e(__('Failed jobs')); ?>: <span class="font-semibold tabular-nums"><?php echo e(number_format($readiness['failed_jobs'] ?? 0)); ?></span></p>
        </div>

        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Worker Commands')); ?></h3>
            <div class="space-y-3">
                <?php $__currentLoopData = $readiness['worker_commands'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="rounded-lg border border-erp-border bg-erp-page px-3 py-2">
                        <p class="text-sm font-medium text-erp-primary"><?php echo e($item['label']); ?></p>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($item['description']); ?></p>
                        <pre class="mt-2 overflow-x-auto rounded bg-slate-900 p-2 text-[11px] text-slate-100"><?php echo e($item['command']); ?></pre>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e(__('Scheduler Checklist')); ?></h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-erp-border text-left text-[11px] uppercase tracking-wide text-slate-500">
                        <th class="px-3 py-2"><?php echo e(__('Command')); ?></th>
                        <th class="px-3 py-2"><?php echo e(__('Schedule')); ?></th>
                        <th class="px-3 py-2"><?php echo e(__('Status')); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $readiness['scheduler_tasks'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b border-erp-border/60">
                            <td class="px-3 py-2 font-mono text-xs"><?php echo e($task['command']); ?></td>
                            <td class="px-3 py-2"><?php echo e($task['schedule']); ?></td>
                            <td class="px-3 py-2">
                                <?php if($task['configured']): ?>
                                    <span class="text-emerald-700"><?php echo e(__('Configured')); ?></span>
                                <?php else: ?>
                                    <span class="text-amber-700"><?php echo e(__('Recommended')); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
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
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\operations\jobs\partials\queue-readiness.blade.php ENDPATH**/ ?>