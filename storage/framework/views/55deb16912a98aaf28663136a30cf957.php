<?php
    $snapshot = $panel['snapshot'] ?? [];
    $estimate = $panel['estimate'] ?? [];
    $ready = (bool) ($panel['ready'] ?? false);
?>

<section class="mb-4">
    <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Order snapshot')); ?></p>
    <dl class="space-y-1.5 text-sm">
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500"><?php echo e(__('Order')); ?></dt>
            <dd class="font-mono font-medium text-slate-900"><?php echo e($snapshot['order_number'] ?? '—'); ?></dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500"><?php echo e(__('Customer')); ?></dt>
            <dd class="text-right font-medium text-slate-900"><?php echo e($snapshot['customer'] ?? '—'); ?></dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500"><?php echo e(__('Product')); ?></dt>
            <dd class="text-right font-medium text-slate-900"><?php echo e($snapshot['product'] ?? '—'); ?></dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500"><?php echo e(__('Quantity')); ?></dt>
            <dd class="font-mono text-slate-900"><?php echo e($snapshot['quantity'] ?? '—'); ?></dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500"><?php echo e(__('Due')); ?></dt>
            <dd class="text-slate-900"><?php echo e($snapshot['due'] ?? '—'); ?></dd>
        </div>
        <div class="flex justify-between gap-2">
            <dt class="text-slate-500"><?php echo e(__('Priority')); ?></dt>
            <dd class="text-slate-900"><?php echo e($snapshot['priority'] ?? '—'); ?></dd>
        </div>
    </dl>
</section>

<section class="mb-4 border-t border-erp-border pt-3">
    <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Production status')); ?></p>
    <ul class="space-y-1.5 text-sm">
        <?php $__currentLoopData = $panel['dashboard'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="flex items-start justify-between gap-2">
                <span class="text-slate-700"><?php echo e($row['label']); ?></span>
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'shrink-0 text-xs font-semibold',
                    'text-emerald-700' => ($row['passed'] ?? false),
                    'text-amber-700' => ! ($row['passed'] ?? false) && ($row['severity'] ?? '') === 'warning',
                    'text-rose-700' => ! ($row['passed'] ?? false) && ($row['severity'] ?? '') !== 'warning',
                ]); ?>">
                    <?php echo e(($row['passed'] ?? false) ? '✓' : '!'); ?>

                </span>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>

    <?php if($ready): ?>
        <p class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-emerald-800">
            <?php echo e(__('Ready for production')); ?>

        </p>
    <?php endif; ?>
</section>

<?php if(($estimate['department'] ?? null) || ($estimate['work_center'] ?? null) || ($estimate['job_card_number'] ?? null)): ?>
    <section class="mb-4 border-t border-erp-border pt-3">
        <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Estimated production')); ?></p>
        <dl class="space-y-1.5 text-sm">
            <?php if($estimate['department'] ?? null): ?>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500"><?php echo e(__('Department')); ?></dt>
                    <dd class="text-slate-900"><?php echo e($estimate['department']); ?></dd>
                </div>
            <?php endif; ?>
            <?php if($estimate['work_center'] ?? null): ?>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500"><?php echo e(__('Work center')); ?></dt>
                    <dd class="text-slate-900"><?php echo e($estimate['work_center']); ?></dd>
                </div>
            <?php endif; ?>
            <?php if($estimate['queue_status'] ?? null): ?>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500"><?php echo e(__('Queue')); ?></dt>
                    <dd class="text-slate-900"><?php echo e($estimate['queue_status']); ?></dd>
                </div>
            <?php endif; ?>
            <?php if($estimate['job_card_number'] ?? null): ?>
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500"><?php echo e(__('Job')); ?></dt>
                    <dd class="font-mono text-slate-900"><?php echo e($estimate['job_card_number']); ?></dd>
                </div>
            <?php endif; ?>
        </dl>
    </section>
<?php else: ?>
    <section class="mb-4 border-t border-erp-border pt-3">
        <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Estimated production')); ?></p>
        <p class="text-xs text-slate-500"><?php echo e(__('Department and work center are assigned when the job is released to the queue.')); ?></p>
    </section>
<?php endif; ?>

<section class="border-t border-erp-border pt-3">
    <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Warnings')); ?></p>
    <?php if(count($panel['warnings'] ?? []) === 0 && $ready): ?>
        <p class="text-sm text-emerald-700"><?php echo e(__('None — safe to release.')); ?></p>
    <?php elseif(count($panel['warnings'] ?? []) === 0): ?>
        <p class="text-sm text-slate-600"><?php echo e(__('Resolve readiness items on the left before releasing.')); ?></p>
    <?php else: ?>
        <ul class="space-y-2">
            <?php $__currentLoopData = $panel['warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'rounded-lg border px-3 py-2 text-xs',
                    'border-rose-200 bg-rose-50 text-rose-900' => ($warning['severity'] ?? '') === 'blocker',
                    'border-amber-200 bg-amber-50 text-amber-900' => ($warning['severity'] ?? '') !== 'blocker',
                ]); ?>">
                    <p class="font-semibold">⚠ <?php echo e($warning['title'] ?? __('Attention')); ?></p>
                    <?php if(! empty($warning['message'])): ?>
                        <p class="mt-0.5"><?php echo e($warning['message']); ?></p>
                    <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\sales\desk\partials\walk-in-panel\release.blade.php ENDPATH**/ ?>