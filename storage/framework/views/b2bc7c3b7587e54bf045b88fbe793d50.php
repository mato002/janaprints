<?php
    $toneClasses = fn (string $tone) => match ($tone) {
        'blue' => 'text-blue-600',
        'indigo' => 'text-indigo-600',
        'amber' => 'text-amber-600',
        'emerald' => 'text-emerald-600',
        'violet' => 'text-violet-600',
        'rose' => 'text-rose-600',
        'slate' => 'text-slate-600',
        default => 'text-erp-primary',
    };
?>

<div class="designer-desk-metrics mb-4 space-y-3">
    <div class="rounded-xl border border-erp-border bg-white p-3 shadow-sm">
        <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Operational')); ?></p>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-7">
            <?php $__currentLoopData = $summary['operational']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="designer-desk-kpi rounded-lg border border-slate-100 bg-slate-50/50 px-3 py-2.5 text-center">
                    <p class="text-xl font-bold tabular-nums <?php echo e($toneClasses($card['tone'] ?? 'primary')); ?>"><?php echo e($card['value']); ?></p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($card['label']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="rounded-xl border border-erp-border/80 bg-gradient-to-r from-slate-50 to-white p-3">
        <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Performance')); ?></p>
        <div class="grid grid-cols-2 gap-2 lg:grid-cols-4">
            <?php $__currentLoopData = $summary['performance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-lg border border-white bg-white/80 px-3 py-2.5 text-center shadow-sm">
                    <p class="text-lg font-bold tabular-nums <?php echo e($toneClasses($card['tone'] ?? 'slate')); ?>"><?php echo e($card['value']); ?></p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($card['label']); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/desk/partials/summary-strip.blade.php ENDPATH**/ ?>