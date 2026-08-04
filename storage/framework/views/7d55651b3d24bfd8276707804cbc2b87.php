<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $health = $workQueue['health'] ?? ['percent' => 100, 'label' => __('Healthy'), 'tone' => 'emerald', 'detail' => ''];
    $toneClasses = fn (string $tone) => match ($tone) {
        'amber' => 'text-amber-600',
        'rose' => 'text-rose-600',
        'blue' => 'text-blue-600',
        'emerald' => 'text-emerald-600',
        default => 'text-erp-primary',
    };
    $healthBadge = match ($health['tone'] ?? 'emerald') {
        'emerald' => 'bg-emerald-600 text-white',
        'amber' => 'bg-amber-500 text-white',
        'rose' => 'bg-rose-600 text-white',
        default => 'bg-slate-700 text-white',
    };
    $frame = WorkspaceEmbed::turboFrame();
?>

<section class="store-desk-health mb-3 rounded-xl border border-erp-border bg-white p-3 shadow-sm" aria-label="<?php echo e(__('Store health')); ?>">
    <div class="mb-2 flex flex-wrap items-center justify-between gap-2 px-1">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Store health')); ?></p>
            <p class="text-xs text-slate-500"><?php echo e($health['detail'] ?? ''); ?></p>
        </div>
        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold <?php echo e($healthBadge); ?>">
            <span class="tabular-nums"><?php echo e((int) ($health['percent'] ?? 0)); ?>%</span>
            <span><?php echo e($health['label'] ?? ''); ?></span>
        </span>
    </div>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        <?php $__currentLoopData = $workQueue['summary'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(! empty($card['url'])): ?>
                <a
                    href="<?php echo e(WorkspaceEmbed::url($card['url'])); ?>"
                    class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'rounded-lg border border-slate-100 bg-slate-50/60 px-3 py-2 text-center transition hover:border-erp-accent/30 hover:bg-erp-accent/5',
                        'ring-1 ring-amber-300/60' => ($card['highlight'] ?? false) && ($card['tone'] ?? '') === 'amber',
                        'ring-1 ring-rose-300/60' => ($card['highlight'] ?? false) && ($card['tone'] ?? '') === 'rose',
                    ]); ?>"
                    data-turbo-frame="<?php echo e($frame); ?>"
                    data-turbo-action="advance"
                >
                    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-xl font-bold tabular-nums', $toneClasses($card['tone'] ?? 'primary')]); ?>"><?php echo e($card['value']); ?></p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($card['label']); ?></p>
                </a>
            <?php else: ?>
                <div class="rounded-lg border border-slate-100 bg-slate-50/60 px-3 py-2 text-center">
                    <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-xl font-bold tabular-nums', $toneClasses($card['tone'] ?? 'primary')]); ?>"><?php echo e($card['value']); ?></p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($card['label']); ?></p>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\store\desk\partials\summary-strip.blade.php ENDPATH**/ ?>