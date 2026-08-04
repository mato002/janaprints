<?php ($chain = $tabData['chain'] ?? []); ?>

<div class="job-360__traceability">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('End-to-end traceability')); ?></h3>

    <ol class="job-360__chain flex flex-col gap-0 sm:flex-row sm:flex-wrap sm:items-stretch">
        <?php $__currentLoopData = $chain; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="job-360__chain-step flex min-w-0 flex-1 flex-col sm:max-w-[11rem]">
                <div class="rounded-lg border border-erp-border bg-erp-page p-3 h-full <?php echo e(($step['state'] ?? '') === 'placeholder' ? 'border-dashed' : ''); ?>">
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($step['label']); ?></span>
                    <?php if(! empty($step['placeholder'])): ?>
                        <p class="mt-2 text-xs text-slate-500"><?php echo e($step['placeholder_message'] ?? __('Coming soon')); ?></p>
                    <?php elseif(! empty($step['url'])): ?>
                        <a href="<?php echo e($step['url']); ?>" class="mt-1 block text-sm font-semibold text-erp-accent hover:text-erp-accent-hover" data-turbo-frame="erp-main">
                            <?php echo e($step['reference']); ?>

                        </a>
                    <?php else: ?>
                        <p class="mt-1 text-sm font-medium text-erp-primary"><?php echo e($step['reference']); ?></p>
                    <?php endif; ?>
                    <?php if(! empty($step['state']) && $step['state'] !== 'placeholder'): ?>
                        <span class="erp-badge erp-badge--draft mt-2 text-[10px]"><?php echo e($step['state']); ?></span>
                    <?php endif; ?>
                </div>
                <?php if(! $loop->last): ?>
                    <span class="job-360__chain-arrow hidden sm:inline text-slate-300 px-1 self-center" aria-hidden="true">→</span>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/job-cards/workspace/tabs/traceability.blade.php ENDPATH**/ ?>