<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $frame = WorkspaceEmbed::turboFrame();
    $stages = $pipelineStages ?? [];
?>

<?php if(count($stages) > 0): ?>
    <section class="mb-3 rounded-xl border border-erp-border bg-white p-3 shadow-sm" aria-label="<?php echo e(__('Buying pipeline')); ?>">
        <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-widest text-slate-400"><?php echo e(__('Pipeline')); ?></p>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a
                    href="<?php echo e(WorkspaceEmbed::url($stage['url'])); ?>"
                    class="relative rounded-lg border border-slate-100 bg-slate-50/60 px-3 py-2 transition hover:border-erp-accent/30 hover:bg-erp-accent/5"
                    data-turbo-frame="<?php echo e($frame); ?>"
                    data-turbo-action="advance"
                >
                    <p class="text-lg font-bold tabular-nums text-erp-primary"><?php echo e((int) ($stage['count'] ?? 0)); ?></p>
                    <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-500"><?php echo e($stage['label']); ?></p>
                    <?php if($index < count($stages) - 1): ?>
                        <span class="pointer-events-none absolute -right-1 top-1/2 hidden -translate-y-1/2 text-slate-300 sm:block" aria-hidden="true">→</span>
                    <?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/procurement/desk/partials/pipeline.blade.php ENDPATH**/ ?>