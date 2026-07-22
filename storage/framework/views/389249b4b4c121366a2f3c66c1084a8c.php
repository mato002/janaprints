<?php
    $chain = $chain ?? [];
?>

<ol class="job-360-traceability" role="list">
    <?php $__currentLoopData = $chain; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php switch($step['badge_state'] ?? 'pending'):
            case ('complete'): ?>
                <?php ($badgeClass = 'bg-emerald-100 text-emerald-800'); ?>
                <?php break; ?>
            <?php case ('pending'): ?>
                <?php ($badgeClass = 'bg-amber-100 text-amber-800'); ?>
                <?php break; ?>
            <?php case ('failed'): ?>
                <?php ($badgeClass = 'bg-red-100 text-red-800'); ?>
                <?php break; ?>
            <?php case ('inactive'): ?>
                <?php ($badgeClass = 'bg-slate-50 text-slate-500 border border-dashed border-slate-300'); ?>
                <?php break; ?>
            <?php case ('not_linked'): ?>
            <?php case ('missing'): ?>
                <?php ($badgeClass = 'bg-slate-100 text-slate-600'); ?>
                <?php break; ?>
            <?php default: ?>
                <?php ($badgeClass = 'bg-slate-100 text-slate-600'); ?>
        <?php endswitch; ?>
        <li class="job-360-traceability__step job-360-traceability__step--<?php echo e($step['badge_state'] ?? 'pending'); ?>">
            <div class="job-360-traceability__marker" aria-hidden="true">
                <span class="job-360-traceability__dot"></span>
            </div>
            <div class="job-360-traceability__body rounded-lg border border-erp-border bg-erp-card p-4 shadow-card <?php echo e(! empty($step['empty']) ? 'border-dashed' : ''); ?>">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <h4 class="text-sm font-semibold text-erp-primary"><?php echo e($step['label']); ?></h4>
                    <span class="erp-badge shrink-0 text-[10px] uppercase tracking-wide <?php echo e($badgeClass); ?>">
                        <?php echo e($step['badge']); ?>

                    </span>
                </div>

                <?php if(! empty($step['empty'])): ?>
                    <p class="mt-2 text-sm font-medium text-slate-600"><?php echo e($step['reference']); ?></p>
                    <?php if(! empty($step['empty_message'])): ?>
                        <p class="mt-1 text-xs text-slate-500"><?php echo e($step['empty_message']); ?></p>
                    <?php endif; ?>
                <?php elseif(! empty($step['url'])): ?>
                    <a
                        href="<?php echo e($step['url']); ?>"
                        class="mt-2 block text-sm font-semibold text-erp-accent hover:text-erp-accent-hover"
                        data-turbo-frame="erp-main"
                    ><?php echo e($step['reference']); ?></a>
                <?php else: ?>
                    <p class="mt-2 text-sm font-semibold text-erp-primary"><?php echo e($step['reference']); ?></p>
                <?php endif; ?>

                <?php if(! empty($step['detail'])): ?>
                    <p class="mt-1 text-xs text-slate-500"><?php echo e($step['detail']); ?></p>
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ol>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\traceability-chain.blade.php ENDPATH**/ ?>