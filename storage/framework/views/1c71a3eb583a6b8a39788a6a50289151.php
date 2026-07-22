<?php
    $primary = $primaryAction ?? null;
    $secondary = $secondaryActions ?? [];
    $links = $linkActions ?? [];
    $completion = $completion ?? ['eligible' => false, 'blockers' => []];
?>

<div class="job-360-workflow mb-4 rounded-lg border border-erp-border bg-white px-4 py-3">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <?php if($primary): ?>
                <?php if(($primary['type'] ?? '') === 'post'): ?>
                    <form method="POST" action="<?php echo e($primary['url']); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="<?php echo e(($primary['variant'] ?? '') === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary'); ?> text-sm">
                            <?php echo e($primary['label']); ?>

                        </button>
                    </form>
                <?php else: ?>
                    <a
                        href="<?php echo e($primary['url']); ?>"
                        class="<?php echo e(($primary['variant'] ?? '') === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary'); ?> text-sm"
                        data-turbo-frame="erp-main"
                        data-turbo-action="advance"
                    ><?php echo e($primary['label']); ?></a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('production.outputs.post')): ?>
                <?php if(($completion['eligible'] ?? false) || ($finishedItems ?? collect())->isNotEmpty()): ?>
                    <button type="button" class="erp-btn-secondary text-sm" data-open-dialog="complete-fg-modal">
                        <?php echo e(__('Post finished goods')); ?>

                    </button>
                <?php endif; ?>
            <?php endif; ?>

            <?php $__currentLoopData = $secondary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(($action['type'] ?? '') === 'post'): ?>
                    <form method="POST" action="<?php echo e($action['url']); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="erp-btn-secondary text-sm"><?php echo e($action['label']); ?></button>
                    </form>
                <?php else: ?>
                    <a href="<?php echo e($action['url']); ?>" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main"><?php echo e($action['label']); ?></a>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('transition', $jobCard)): ?>
                <?php if($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::Cancelled)): ?>
                    <form method="POST" action="<?php echo e(route('admin.production.job-cards.cancel', $jobCard)); ?>" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="text-sm text-red-600 hover:underline"><?php echo e(__('Cancel job')); ?></button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $jobCard)): ?>
                <form method="POST" action="<?php echo e(route('admin.production.job-cards.destroy', $jobCard)); ?>" class="inline" onsubmit="return confirm(<?php echo \Illuminate\Support\Js::from(__('Permanently delete this job card? This cannot be undone.'))->toHtml() ?>)">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="text-sm text-red-700 hover:underline"><?php echo e(__('Delete job')); ?></button>
                </form>
            <?php endif; ?>
        </div>

        <?php if(count($links) > 0): ?>
            <details class="relative text-sm">
                <summary class="cursor-pointer list-none text-slate-600 hover:text-erp-primary [&::-webkit-details-marker]:hidden">
                    <?php echo e(__('Related links')); ?> ▾
                </summary>
                <div class="absolute right-0 z-10 mt-1 min-w-[11rem] rounded-md border border-erp-border bg-white py-1 shadow-lg">
                    <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a
                            href="<?php echo e($link['url']); ?>"
                            class="block px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50"
                            <?php if(($link['target'] ?? null) === '_blank'): ?> target="_blank" rel="noopener" <?php else: ?> data-turbo-frame="erp-main" <?php endif; ?>
                        ><?php echo e($link['label']); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </details>
        <?php endif; ?>
    </div>

    <?php if(! ($completion['eligible'] ?? true) && ! empty($completion['blockers'] ?? [])): ?>
        <p class="mt-2 text-xs text-amber-800">
            <?php echo e(implode(' · ', $completion['blockers'])); ?>

        </p>
    <?php endif; ?>

    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('schedule', $jobCard)): ?>
        <form method="POST" action="<?php echo e(route('admin.production.job-cards.schedule', $jobCard)); ?>" class="mt-3 flex flex-wrap items-end gap-2 border-t border-erp-border pt-3">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Planned start')); ?></label>
                <input type="date" name="planned_start_date" class="erp-input text-sm py-1" value="<?php echo e($jobCard->planned_start_date?->format('Y-m-d')); ?>" required>
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-500"><?php echo e(__('Planned end')); ?></label>
                <input type="date" name="planned_end_date" class="erp-input text-sm py-1" value="<?php echo e($jobCard->planned_end_date?->format('Y-m-d')); ?>" required>
            </div>
            <button type="submit" class="erp-btn-secondary text-sm py-1"><?php echo e(__('Update schedule')); ?></button>
        </form>
    <?php endif; ?>
</div>

<?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('production.outputs.post')): ?>
    <?php echo $__env->make('admin.production.job-cards.workspace.partials.complete-finished-goods-modal', [
        'jobCard' => $jobCard,
        'completion' => $completion,
        'finishedItems' => $finishedItems ?? collect(),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/partials/workflow-bar.blade.php ENDPATH**/ ?>