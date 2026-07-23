<?php
    $toneClasses = fn (string $tone, bool $highlight) => match (true) {
        $highlight && $tone === 'amber' => 'text-amber-600',
        $highlight && $tone === 'rose' => 'text-rose-600',
        $highlight && $tone === 'blue' => 'text-blue-600',
        default => 'text-erp-primary',
    };
?>

<div class="mb-4">
    <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__("Today's store work")); ?></p>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
        <?php $__currentLoopData = $workQueue['summary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-lg border border-erp-border bg-white px-4 py-3">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-500"><?php echo e($card['label']); ?></p>
                <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mt-1 text-2xl font-bold tabular-nums', $toneClasses($card['tone'], $card['highlight'])]); ?>"><?php echo e($card['value']); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\partials\summary-strip.blade.php ENDPATH**/ ?>