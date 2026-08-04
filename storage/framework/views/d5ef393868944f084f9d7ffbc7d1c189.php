<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $needsAttention = $needsAttention ?? [];
    $frame = WorkspaceEmbed::turboFrame();
?>

<section class="store-desk-attention rounded-xl border border-erp-border bg-white shadow-sm" aria-label="<?php echo e(__('Needs attention')); ?>">
    <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
        <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Needs attention')); ?></h2>
    </div>

    <ul class="divide-y divide-slate-100">
        <?php $__empty_1 = true; $__currentLoopData = $needsAttention; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $dot = match ($item['severity'] ?? 'warning') {
                    'critical' => 'bg-rose-500',
                    'ok' => 'bg-emerald-500',
                    default => 'bg-amber-400',
                };
            ?>
            <li>
                <?php if(! empty($item['url'])): ?>
                    <a
                        href="<?php echo e(WorkspaceEmbed::url($item['url'])); ?>"
                        class="flex items-center justify-between gap-3 px-3 py-2 text-sm transition hover:bg-slate-50"
                        data-turbo-frame="<?php echo e($frame); ?>"
                        data-turbo-action="advance"
                    >
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <span class="h-2 w-2 shrink-0 rounded-full <?php echo e($dot); ?>" aria-hidden="true"></span>
                            <span class="truncate font-medium text-slate-900"><?php echo e($item['label']); ?></span>
                        </span>
                        <?php if(($item['count'] ?? 0) > 0): ?>
                            <span class="shrink-0 tabular-nums text-xs font-semibold text-slate-600"><?php echo e($item['count']); ?></span>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <div class="flex items-center gap-2 px-3 py-2 text-sm text-slate-600">
                        <span class="h-2 w-2 shrink-0 rounded-full <?php echo e($dot); ?>" aria-hidden="true"></span>
                        <span><?php echo e($item['label']); ?></span>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <li class="px-3 py-3 text-sm text-slate-500"><?php echo e(__('Nothing requires attention right now.')); ?></li>
        <?php endif; ?>
    </ul>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\procurement\desk\partials\needs-attention.blade.php ENDPATH**/ ?>