<?php
    $readiness = $materialReadiness ?? null;
?>

<?php if(is_array($readiness)): ?>
    <?php
        $status = $readiness['status'] ?? 'unknown';
        $ready = (bool) ($readiness['ready'] ?? false);
        $percent = (int) ($readiness['percent'] ?? 0);
        $missing = $readiness['missing'] ?? [];
        $materialsUrl = $readiness['materials_url'] ?? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'materials']);
        $tone = match ($status) {
            'ready' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
            'blocked' => 'border-rose-200 bg-rose-50 text-rose-950',
            default => 'border-amber-200 bg-amber-50 text-amber-950',
        };
        $badgeTone = match ($status) {
            'ready' => 'bg-emerald-600 text-white',
            'blocked' => 'bg-rose-600 text-white',
            default => 'bg-amber-500 text-white',
        };
    ?>

    <section class="mt-3 rounded-lg border px-4 py-3 <?php echo e($tone); ?>" aria-label="<?php echo e(__('Material readiness')); ?>">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-sm font-semibold tracking-wide"><?php echo e(__('Material Readiness')); ?></h2>
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide <?php echo e($badgeTone); ?>">
                        <?php echo e($percent); ?>% · <?php echo e($readiness['label'] ?? ''); ?>

                    </span>
                    <?php if($ready): ?>
                        <span class="text-xs font-medium"><?php echo e(__('Ready for release')); ?></span>
                    <?php elseif(! ($readiness['has_requirements'] ?? false)): ?>
                        <span class="text-xs font-medium"><?php echo e(__('Requirements not generated')); ?></span>
                    <?php else: ?>
                        <span class="text-xs font-medium"><?php echo e(__('Release blocked')); ?></span>
                    <?php endif; ?>
                </div>
                <p class="mt-1 text-xs leading-relaxed opacity-90"><?php echo e($readiness['detail'] ?? ''); ?></p>

                <?php if(count($missing) > 0): ?>
                    <ul class="mt-2 space-y-1 text-xs">
                        <?php $__currentLoopData = array_slice($missing, 0, 5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <span class="font-semibold"><?php echo e($line['item']); ?></span>
                                <?php if(! empty($line['sku'])): ?>
                                    <span class="opacity-70">(<?php echo e($line['sku']); ?>)</span>
                                <?php endif; ?>
                                — <?php echo e(__('short by')); ?>

                                <span class="font-semibold tabular-nums">
                                    <?php echo e(rtrim(rtrim(number_format((float) $line['shortfall'], 3, '.', ''), '0'), '.')); ?>

                                    <?php echo e($line['unit'] ?? ''); ?>

                                </span>
                                <span class="opacity-70">
                                    (<?php echo e(__('available')); ?>

                                    <?php echo e(rtrim(rtrim(number_format((float) $line['available'], 3, '.', ''), '0'), '.')); ?>)
                                </span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if(count($missing) > 5): ?>
                            <li class="opacity-80"><?php echo e(__('and :count more…', ['count' => count($missing) - 5])); ?></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <a href="<?php echo e($materialsUrl); ?>" class="shrink-0 text-xs font-semibold underline underline-offset-2 hover:opacity-80">
                <?php echo e($ready ? __('View materials') : __('Resolve shortages')); ?>

            </a>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\job-cards\workspace\partials\material-readiness-banner.blade.php ENDPATH**/ ?>