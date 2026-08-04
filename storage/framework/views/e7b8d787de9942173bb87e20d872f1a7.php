<?php
    use App\Support\Navigation\WorkspaceEmbed;
?>

<div class="mb-4 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
    <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($kpi['clickable'] && ! empty($kpi['url'])): ?>
            <a
                href="<?php echo e(WorkspaceEmbed::url($kpi['url'])); ?>"
                data-turbo-frame="<?php echo e(WorkspaceEmbed::turboFrame()); ?>"
                data-turbo-action="advance"
                class="rounded-md border border-erp-border bg-white px-3 py-2 transition-colors hover:border-erp-primary/40 hover:bg-erp-primary/5"
            >
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500"><?php echo e($kpi['label']); ?></p>
                <p class="mt-0.5 text-lg font-semibold tabular-nums text-erp-primary"><?php echo e($kpi['value']); ?></p>
            </a>
        <?php else: ?>
            <div class="rounded-md border border-erp-border bg-white px-3 py-2">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500"><?php echo e($kpi['label']); ?></p>
                <p class="mt-0.5 text-lg font-semibold tabular-nums text-erp-primary"><?php echo e($kpi['value']); ?></p>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\reports\operational-registers\partials\kpi-strip.blade.php ENDPATH**/ ?>