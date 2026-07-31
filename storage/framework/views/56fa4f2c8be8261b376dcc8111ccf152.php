<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $frame = WorkspaceEmbed::turboFrame();
    $queueItems = $queueItems ?? [];
?>

<section class="rounded-xl border border-erp-border bg-white shadow-sm" aria-label="<?php echo e(__('Work queue')); ?>">
    <div class="flex items-center justify-between gap-2 border-b border-erp-border px-3 py-2">
        <h2 class="text-sm font-semibold text-slate-900"><?php echo e(__('Work queue')); ?></h2>
        <a
            href="<?php echo e(WorkspaceEmbed::url(\App\Support\Procurement\ProcurementDeskViews::deskUrl(\App\Support\Procurement\ProcurementDeskViews::REQUESTS))); ?>"
            class="text-[11px] font-semibold text-erp-accent hover:underline"
            data-turbo-frame="<?php echo e($frame); ?>"
            data-turbo-action="advance"
        ><?php echo e(__('All requests')); ?></a>
    </div>

    <?php if(count($queueItems) === 0): ?>
        <div class="px-3 py-5 text-center text-sm text-slate-500"><?php echo e(__('No open buy work right now.')); ?></div>
    <?php else: ?>
        <ul class="divide-y divide-slate-100">
            <?php $__currentLoopData = $queueItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <a
                        href="<?php echo e(WorkspaceEmbed::url($item['url'])); ?>"
                        class="flex items-start justify-between gap-3 px-3 py-2.5 text-sm transition hover:bg-slate-50"
                        data-turbo-frame="<?php echo e($frame); ?>"
                        data-turbo-action="advance"
                    >
                        <span class="min-w-0">
                            <span class="block truncate font-medium text-slate-900"><?php echo e($item['title']); ?></span>
                            <span class="mt-0.5 block truncate text-xs text-slate-500">
                                <span class="font-mono"><?php echo e($item['label']); ?></span>
                                <span class="mx-1 text-slate-300">·</span>
                                <?php echo e($item['meta']); ?>

                            </span>
                        </span>
                        <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'shrink-0 text-[11px] font-semibold',
                            'text-rose-700' => ($item['tone'] ?? '') === 'rose',
                            'text-amber-700' => ($item['tone'] ?? '') === 'amber',
                            'text-blue-700' => ($item['tone'] ?? '') === 'blue',
                            'text-emerald-700' => ($item['tone'] ?? '') === 'emerald',
                            'text-slate-600' => ! in_array($item['tone'] ?? '', ['rose', 'amber', 'blue', 'emerald'], true),
                        ]); ?>"><?php echo e($item['status']); ?></span>
                    </a>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    <?php endif; ?>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/procurement/desk/partials/work-queue.blade.php ENDPATH**/ ?>