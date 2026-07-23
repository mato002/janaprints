<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $needsAttention = $needsAttention ?? [];
    $lowStockItems = $lowStockItems ?? [];
    $frame = WorkspaceEmbed::turboFrame();
?>

<section class="store-desk-attention rounded-xl border border-erp-border bg-white shadow-sm" aria-label="<?php echo e(__('Needs attention')); ?>">
    <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
        <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Needs attention')); ?></h2>
        <?php if(count($lowStockItems) > 0): ?>
            <a href="<?php echo e($reorderAlertsUrl ?? route('admin.store.desk.reorder-alerts')); ?>" class="text-[11px] font-semibold text-erp-accent hover:underline" data-erp-modal-open><?php echo e(__('All alerts')); ?></a>
        <?php endif; ?>
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
                        href="<?php echo e(($item['modal'] ?? false) ? $item['url'] : WorkspaceEmbed::url($item['url'])); ?>"
                        class="flex items-center justify-between gap-3 px-3 py-2 text-sm transition hover:bg-slate-50"
                        <?php if($item['modal'] ?? false): ?>
                            data-erp-modal-open
                        <?php else: ?>
                            data-turbo-frame="<?php echo e($frame); ?>"
                            data-turbo-action="advance"
                        <?php endif; ?>
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

    <?php if(count($lowStockItems) > 0): ?>
        <div class="border-t border-erp-border bg-slate-50/50 px-3 py-2">
            <p class="mb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Low stock')); ?></p>
            <ul class="space-y-1.5">
                <?php $__currentLoopData = $lowStockItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-center justify-between gap-2 text-xs">
                        <span class="min-w-0 truncate">
                            <span class="font-medium text-slate-900"><?php echo e($item['name']); ?></span>
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'ml-1 tabular-nums',
                                'text-rose-700' => $item['urgent'] ?? false,
                                'text-amber-700' => ! ($item['urgent'] ?? false),
                            ]); ?>"><?php echo e($item['remaining_label']); ?></span>
                        </span>
                        <a href="<?php echo e($item['url'] ?? $reorderAlertsUrl); ?>" class="shrink-0 font-semibold text-erp-accent hover:underline" data-erp-modal-open><?php echo e($item['action'] ?? __('Purchase')); ?></a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\partials\needs-attention.blade.php ENDPATH**/ ?>