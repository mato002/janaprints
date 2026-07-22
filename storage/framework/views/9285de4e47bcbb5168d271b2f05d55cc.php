<?php
    $presentRow = function ($queue) use ($commandCenter, $workspace, $activeDepartment) {
        if ($commandCenter) {
            return $commandCenter->presentCommandRow($queue, $activeDepartment);
        }

        return $workspace->presentRow($queue);
    };

    $statusColumns = [
        'production_status' => 'production_status_variant',
        'qc_status' => 'qc_status_variant',
        'dispatch_status' => 'dispatch_status_variant',
        'payment_status' => 'payment_status_variant',
        'order_status' => 'order_status_variant',
    ];

    $displayColumns = $columns !== [] ? $columns : [
        ['key' => 'priority_label', 'label' => __('Priority'), 'class' => ''],
        ['key' => 'job_card_number', 'label' => __('Job card'), 'class' => 'font-mono text-xs'],
        ['key' => 'customer_name', 'label' => __('Customer'), 'class' => ''],
        ['key' => 'product', 'label' => __('Product'), 'class' => ''],
        ['key' => 'due_date', 'label' => __('Due'), 'class' => 'tabular-nums'],
        ['key' => 'production_status', 'label' => __('Status'), 'class' => ''],
    ];
    $colCount = count($displayColumns) + 1;
?>

<div class="border-b border-erp-border px-4 py-3">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Department operational register')); ?></h2>
    <p class="mt-0.5 text-xs text-slate-500"><?php echo e(__('Live ERP data — ordered by priority and due date')); ?></p>
</div>

<div class="hidden md:block overflow-x-auto">
    <table class="erp-table w-full text-sm">
        <thead>
            <tr>
                <?php $__currentLoopData = $displayColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th><?php echo e($column['label']); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <th class="erp-table-actions-col"><?php echo e(__('Actions')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $queues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $row = $presentRow($queue); ?>
                <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'bg-red-50/50' => $row['row_tone'] === 'danger',
                    'bg-amber-50/40' => $row['row_tone'] === 'warning',
                ]); ?>">
                    <?php $__currentLoopData = $displayColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $key = $column['key'];
                            $value = $row[$key] ?? '—';
                            $variantKey = $statusColumns[$key] ?? null;
                        ?>
                        <td class="<?php echo \Illuminate\Support\Arr::toCssClasses([$column['class'] ?? '', 'whitespace-nowrap' => in_array($key, ['due_date', 'date'], true)]); ?>">
                            <?php if($key === 'job_card_number' && ! empty($row['job_360_url'])): ?>
                                <a href="<?php echo e($row['job_360_url']); ?>" class="font-mono text-xs text-erp-primary hover:underline" data-turbo-frame="erp-main"><?php echo e($value); ?></a>
                            <?php elseif($key === 'product'): ?>
                                <div class="font-medium max-w-xs truncate" title="<?php echo e($value); ?>"><?php echo e($value); ?></div>
                                <?php if(! empty($row['status_badges'])): ?>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        <?php $__currentLoopData = $row['status_badges']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php echo $__env->make('admin.production.queue.partials.status-badge', [
                                                'label' => $badge['label'],
                                                'variant' => $badge['variant'] ?? 'neutral',
                                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php endif; ?>
                            <?php elseif($variantKey && filled($value) && $value !== '—'): ?>
                                <?php echo $__env->make('admin.production.queue.partials.status-badge', [
                                    'label' => $value,
                                    'variant' => $row[$variantKey] ?? 'neutral',
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php elseif($key === 'due_date' && $row['days_remaining'] !== null): ?>
                                <?php echo e($value); ?>

                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'block text-[10px]',
                                    'text-red-600 font-medium' => $row['days_remaining'] < 0,
                                    'text-slate-500' => $row['days_remaining'] >= 0,
                                ]); ?>"><?php echo e($row['days_remaining']); ?>d</span>
                            <?php else: ?>
                                <?php echo e($value); ?>

                            <?php endif; ?>
                        </td>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <td class="erp-table-actions-col">
                        <?php echo $__env->make('admin.production.queue.partials.row-actions', ['row' => $row], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e($colCount); ?>">
                        <?php echo $__env->make('admin.production.queue.partials.empty-state', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="md:hidden divide-y divide-erp-border">
    <?php $__empty_1 = true; $__currentLoopData = $queues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php $row = $presentRow($queue); ?>
        <div class="<?php echo \Illuminate\Support\Arr::toCssClasses([
            'p-4 space-y-2',
            'bg-red-50/50' => $row['row_tone'] === 'danger',
            'bg-amber-50/40' => $row['row_tone'] === 'warning',
        ]); ?>">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <?php if(! empty($row['job_360_url'])): ?>
                        <a href="<?php echo e($row['job_360_url']); ?>" class="font-mono text-sm font-semibold text-erp-primary" data-turbo-frame="erp-main"><?php echo e($row['job_card_number']); ?></a>
                    <?php else: ?>
                        <p class="font-mono text-sm font-semibold"><?php echo e($row['job_card_number']); ?></p>
                    <?php endif; ?>
                    <p class="text-sm"><?php echo e($row['customer_name']); ?></p>
                    <p class="text-sm font-medium text-erp-primary"><?php echo e($row['product']); ?></p>
                </div>
                <?php echo $__env->make('admin.production.queue.partials.status-badge', [
                    'label' => $row['production_status'] ?? $row['operational_status'],
                    'variant' => $row['production_status_variant'] ?? $row['operational_variant'] ?? 'neutral',
                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <?php if(! empty($row['status_badges'])): ?>
                <div class="flex flex-wrap gap-1">
                    <?php $__currentLoopData = $row['status_badges']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('admin.production.queue.partials.status-badge', [
                            'label' => $badge['label'],
                            'variant' => $badge['variant'] ?? 'neutral',
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
            <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-slate-600">
                <div><dt class="inline"><?php echo e(__('Due')); ?>:</dt> <dd class="inline font-medium"><?php echo e($row['due_date'] ?? '—'); ?></dd></div>
                <div><dt class="inline"><?php echo e(__('Qty')); ?>:</dt> <dd class="inline font-medium"><?php echo e($row['quantity'] ?? '—'); ?></dd></div>
                <div><dt class="inline"><?php echo e(__('Operator')); ?>:</dt> <dd class="inline font-medium"><?php echo e($row['operator_name']); ?></dd></div>
                <div><dt class="inline"><?php echo e(__('Machine')); ?>:</dt> <dd class="inline font-medium"><?php echo e($row['machine_name']); ?></dd></div>
            </dl>
            <div class="flex flex-wrap gap-2 pt-1">
                <?php echo $__env->make('admin.production.queue.partials.row-actions', ['row' => $row, 'compact' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="p-4">
            <?php echo $__env->make('admin.production.queue.partials.empty-state', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?>
</div>

<?php if($queues->hasPages()): ?>
    <div class="border-t border-erp-border px-4 py-3">
        <?php echo e($queues->links()); ?>

    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\queue\partials\table.blade.php ENDPATH**/ ?>