<div
    x-show="!selectedKey"
    x-cloak
    class="designer-desk-idle mt-6"
>
    <?php if(! $has_assignments): ?>
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50/60 px-6 py-8 text-center">
            <p class="text-sm font-medium text-slate-600"><?php echo e(__('No artwork assigned.')); ?></p>
            <p class="mt-1 text-xs text-slate-500"><?php echo e(__('New jobs will appear in your queue when assigned.')); ?></p>
        </div>
    <?php endif; ?>

    <?php if(count($today_activity) > 0): ?>
        <div class="mt-4 rounded-xl border border-erp-border bg-white p-4 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-erp-primary"><?php echo e(__("Today's Activity")); ?></h3>
            <div class="divide-y divide-slate-100">
                <?php $__currentLoopData = $today_activity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $icon = match ($event['tone'] ?? 'neutral') {
                            'success' => '✓',
                            'warning' => '!',
                            'info' => '↑',
                            default => '•',
                        };
                        $iconClass = match ($event['tone'] ?? 'neutral') {
                            'success' => 'text-emerald-600',
                            'warning' => 'text-amber-600',
                            'info' => 'text-blue-600',
                            default => 'text-slate-400',
                        };
                    ?>
                    <div class="flex items-start gap-3 py-2.5 text-sm">
                        <span class="mt-0.5 w-4 shrink-0 text-center font-bold <?php echo e($iconClass); ?>"><?php echo e($icon); ?></span>
                        <span class="w-12 shrink-0 font-mono text-xs text-slate-400"><?php echo e($event['time']); ?></span>
                        <span class="text-slate-700"><?php echo e($event['label']); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php elseif(! $has_assignments): ?>
        <div class="mt-4 rounded-xl border border-erp-border bg-white p-4 text-sm text-slate-500">
            <?php echo e(__('No recent activity yet.')); ?>

        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/artwork/desk/partials/idle-panel.blade.php ENDPATH**/ ?>