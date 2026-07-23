<?php
    $toneClasses = fn (string $tone) => match ($tone) {
        'amber' => 'text-amber-600',
        'indigo' => 'text-indigo-600',
        'emerald' => 'text-emerald-600',
        'violet' => 'text-violet-600',
        'rose' => 'text-rose-600',
        'slate' => 'text-slate-600',
        default => 'text-erp-primary',
    };
?>

<div class="sales-desk-metrics mb-4">
    <div class="rounded-xl border border-erp-border bg-white p-3 shadow-sm">
        <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__("Today's sales work")); ?></p>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
            <?php $__currentLoopData = $workQueue['summary'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(! empty($card['url'])): ?>
                    <a
                        href="<?php echo e($card['url']); ?>"
                        class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            'sales-desk-kpi rounded-lg border border-slate-100 bg-slate-50/50 px-3 py-2.5 text-center transition hover:border-erp-accent/30 hover:bg-erp-accent/5',
                            'ring-2 ring-erp-accent/20' => ($card['value'] ?? 0) > 0 && ($card['tone'] ?? '') === 'amber',
                        ]); ?>"
                        data-turbo-frame="erp-main"
                    >
                        <p class="text-xl font-bold tabular-nums <?php echo e($toneClasses($card['tone'] ?? 'primary')); ?>"><?php echo e($card['value']); ?></p>
                        <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($card['label']); ?></p>
                    </a>
                <?php else: ?>
                    <div class="sales-desk-kpi rounded-lg border border-slate-100 bg-slate-50/50 px-3 py-2.5 text-center">
                        <p class="text-xl font-bold tabular-nums <?php echo e($toneClasses($card['tone'] ?? 'primary')); ?>"><?php echo e($card['value']); ?></p>
                        <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($card['label']); ?></p>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/sales/desk/partials/summary-strip.blade.php ENDPATH**/ ?>