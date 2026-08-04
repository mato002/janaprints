<?php
    $health = $dashboard['communication_health'] ?? null;
    $statusColors = [
        'healthy' => 'bg-emerald-500',
        'warning' => 'bg-amber-400',
        'critical' => 'bg-red-500',
    ];
?>

<?php if($health): ?>
    <section class="exec-integration-health rounded-lg border border-erp-border bg-white p-4" aria-label="<?php echo e(__('Communication health')); ?>">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-erp-primary"><?php echo e(__('Communication Health')); ?></h2>
            <a href="<?php echo e($health['url'] ?? route('admin.communications.email.settings')); ?>" class="text-xs text-erp-accent hover:underline" data-turbo-frame="erp-main"><?php echo e(__('Diagnostics')); ?></a>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-md border border-erp-border px-3 py-2 text-xs">
                <span class="mb-2 flex items-center gap-2 font-medium text-slate-700">
                    <span class="h-2 w-2 rounded-full <?php echo e($statusColors[$health['level']] ?? $statusColors['warning']); ?>"></span>
                    <?php echo e($health['label']); ?>

                </span>
                <span class="text-slate-500"><?php echo e(__('Overall status')); ?></span>
            </div>
            <div class="rounded-md border border-erp-border px-3 py-2 text-xs">
                <span class="block font-medium text-slate-700"><?php echo e($health['failure_rate']); ?>%</span>
                <span class="text-slate-500"><?php echo e(__('Failure rate (7d)')); ?></span>
            </div>
            <div class="rounded-md border border-erp-border px-3 py-2 text-xs">
                <span class="block font-medium text-slate-700"><?php echo e($health['queue_backlog']); ?></span>
                <span class="text-slate-500"><?php echo e(__('Queue backlog')); ?></span>
            </div>
            <div class="rounded-md border border-erp-border px-3 py-2 text-xs">
                <span class="block font-medium text-slate-700"><?php echo e($health['smtp_available'] ? __('Available') : __('Missing')); ?></span>
                <span class="text-slate-500"><?php echo e(__('SMTP')); ?></span>
            </div>
        </div>
    </section>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\dashboard\partials\communication-health.blade.php ENDPATH**/ ?>