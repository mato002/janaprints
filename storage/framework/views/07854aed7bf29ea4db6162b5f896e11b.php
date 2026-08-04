<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<section class="rounded-xl border border-erp-border bg-white shadow-sm" aria-label="<?php echo e(__('Inventory activity')); ?>">
    <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
        <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Inventory activity')); ?></h2>
        <a
            href="<?php echo e(WorkspaceEmbed::url(\App\Support\Inventory\StoreDeskViews::deskUrl(\App\Support\Inventory\StoreDeskViews::MOVEMENTS))); ?>"
            class="text-[11px] font-semibold text-erp-accent hover:underline"
            data-turbo-frame="<?php echo e(WorkspaceEmbed::turboFrame()); ?>"
            data-turbo-action="advance"
        ><?php echo e(__('All movements')); ?></a>
    </div>
    <?php if(count($movementFeed ?? []) === 0): ?>
        <div class="px-3 py-5 text-center text-sm text-slate-500"><?php echo e(__('No stock movements recorded yet.')); ?></div>
    <?php else: ?>
        <ul class="divide-y divide-slate-100">
            <?php $__currentLoopData = $movementFeed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center gap-3 px-3 py-2 text-sm">
                    <span class="w-10 shrink-0 font-mono text-[11px] tabular-nums text-slate-500"><?php echo e($movement['time']); ?></span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-medium text-slate-900"><?php echo e($movement['type']); ?> · <?php echo e($movement['item']); ?></span>
                        <?php if(! empty($movement['warehouse'])): ?>
                            <span class="block truncate text-[11px] text-slate-500"><?php echo e($movement['warehouse']); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'shrink-0 font-mono text-xs font-semibold tabular-nums',
                        'text-emerald-700' => $movement['inbound'],
                        'text-rose-700' => ! $movement['inbound'],
                    ]); ?>"><?php echo e($movement['quantity']); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\partials\movement-feed.blade.php ENDPATH**/ ?>