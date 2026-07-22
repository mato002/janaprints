<div class="procurement-journey">
    <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('Procurement journey')); ?></h3>
    <p class="mt-1 text-xs text-slate-500"><?php echo e($journey['conversion_path']); ?></p>
    <ol class="mt-4 space-y-0">
        <?php $__currentLoopData = $journey['steps']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="procurement-journey__step">
                <div class="flex items-start gap-3">
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-semibold uppercase',
                        'bg-emerald-100 text-emerald-700' => $step['state'] === 'complete',
                        'bg-sky-100 text-sky-700' => $step['state'] === 'active',
                        'bg-slate-100 text-slate-400' => in_array($step['state'], ['pending', 'skipped'], true),
                    ]); ?>">
                        <?php if($step['state'] === 'complete'): ?>
                            ✓
                        <?php elseif($step['state'] === 'skipped'): ?>
                            —
                        <?php else: ?>
                            <?php echo e($loop->iteration); ?>

                        <?php endif; ?>
                    </span>
                    <div class="min-w-0 flex-1 pb-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium text-slate-900"><?php echo e($step['label']); ?></span>
                            <?php if($step['state'] === 'skipped'): ?>
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-slate-500"><?php echo e(__('Skipped')); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if($step['document']): ?>
                            <?php if($step['route']): ?>
                                <a href="<?php echo e($step['route']); ?>" class="mt-1 inline-block text-sm text-erp-primary hover:underline"><?php echo e($step['document']); ?></a>
                            <?php else: ?>
                                <p class="mt-1 text-sm text-slate-600"><?php echo e($step['document']); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="mt-1 text-sm text-slate-400"><?php echo e(__('Pending')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (! ($loop->last)): ?>
                    <div class="ml-3 border-l border-dashed border-slate-200 pl-6 text-center text-slate-300" aria-hidden="true">↓</div>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\procurement\requests\partials\journey-panel.blade.php ENDPATH**/ ?>