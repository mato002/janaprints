<?php
    use App\Support\Navigation\WorkspaceEmbed;

    $detailLinkAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
    $turboFrame = WorkspaceEmbed::turboFrame();

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

    $frozenColumnKeys = ['date', 'job_card_number', 'customer_name'];

    $displayColumns = $columns !== [] ? $columns : [
        ['key' => 'priority_label', 'label' => __('Priority'), 'class' => ''],
        ['key' => 'job_card_number', 'label' => __('Job card'), 'class' => 'font-mono text-xs'],
        ['key' => 'customer_name', 'label' => __('Customer'), 'class' => ''],
        ['key' => 'product', 'label' => __('Product'), 'class' => ''],
        ['key' => 'due_date', 'label' => __('Due'), 'class' => 'tabular-nums text-right'],
        ['key' => 'order_status', 'label' => __('Status'), 'class' => ''],
    ];
    $colCount = count($displayColumns) + 1;

    $frozenIndex = 0;
?>

<?php if (! ($dense ?? false)): ?>
    <div class="border-b border-erp-border px-4 py-2">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-erp-primary"><?php echo e(__('Department operational register')); ?></h2>
        <p class="text-[11px] text-slate-500"><?php echo e(__('Live ERP data — ordered by priority and due date')); ?></p>
    </div>
<?php endif; ?>

<div class="hidden md:block production-queue-register production-queue-register--scroll">
    <table class="erp-table erp-table--grid production-queue-register-table w-full text-sm">
        <thead>
            <tr>
                <?php $__currentLoopData = $displayColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $isFrozen = in_array($column['key'], $frozenColumnKeys, true);
                        $frozenClass = $isFrozen ? 'production-queue-col-frozen production-queue-col-frozen--'.(++$frozenIndex) : '';
                    ?>
                    <th class="<?php echo \Illuminate\Support\Arr::toCssClasses([$frozenClass, 'text-right' => str_contains($column['class'] ?? '', 'text-right')]); ?>"><?php echo e($column['label']); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <th class="erp-table-actions-col production-queue-col-actions"><?php echo e(__('Actions')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $queues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $row = $presentRow($queue); ?>
                <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                    'production-queue-row',
                    'production-queue-row--'.$row['row_urgency'] => ($row['row_urgency'] ?? 'default') !== 'default',
                ]); ?>">
                    <?php $frozenIndex = 0; ?>
                    <?php $__currentLoopData = $displayColumns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $key = $column['key'];
                            $value = $row[$key] ?? '—';
                            $variantKey = $statusColumns[$key] ?? null;
                            $isFrozen = in_array($key, $frozenColumnKeys, true);
                            $frozenClass = $isFrozen ? 'production-queue-col-frozen production-queue-col-frozen--'.(++$frozenIndex) : '';
                        ?>
                        <td class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                            $column['class'] ?? '',
                            $frozenClass,
                            'whitespace-nowrap' => in_array($key, ['due_date', 'date', 'date_sent', 'expected_return'], true),
                        ]); ?>">
                            <?php if($key === 'job_card_number' && ! empty($row['job_360_url'])): ?>
                                <a href="<?php echo e($row['job_360_url']); ?>" class="font-mono text-xs text-erp-primary hover:underline" <?php $__currentLoopData = $detailLinkAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>><?php echo e($value); ?></a>
                            <?php elseif($key === 'product'): ?>
                                <div class="production-queue-product" title="<?php echo e($value); ?>"><?php echo e($value); ?></div>
                            <?php elseif($key === 'progress'): ?>
                                <?php $percent = (int) ($row['progress_percent'] ?? 0); ?>
                                <div class="production-queue-progress" title="<?php echo e(__(':percent% complete', ['percent' => $percent])); ?>">
                                    <div class="production-queue-progress__track">
                                        <div class="production-queue-progress__fill" style="width: <?php echo e($percent); ?>%"></div>
                                    </div>
                                    <span class="production-queue-progress__label tabular-nums"><?php echo e($percent); ?>%</span>
                                </div>
                            <?php elseif($variantKey && filled($value) && $value !== '—'): ?>
                                <div class="space-y-1">
                                    <?php echo $__env->make('admin.production.queue.partials.status-badge', [
                                        'label' => $value,
                                        'variant' => $row[$variantKey] ?? 'neutral',
                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    <?php if(in_array($key, ['order_status', 'production_status'], true) && ! empty($row['status_badges'])): ?>
                                        <div class="flex flex-wrap gap-1">
                                            <?php $__currentLoopData = $row['status_badges']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php echo $__env->make('admin.production.queue.partials.status-badge', [
                                                    'label' => $badge['label'],
                                                    'variant' => $badge['variant'] ?? 'neutral',
                                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
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
                    <td class="erp-table-actions-col production-queue-col-actions">
                        <?php echo $__env->make('admin.production.queue.partials.row-actions', ['row' => $row, 'inline' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
            'p-4 space-y-2 production-queue-row',
            'production-queue-row--'.$row['row_urgency'] => ($row['row_urgency'] ?? 'default') !== 'default',
        ]); ?>">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <?php if(! empty($row['job_360_url'])): ?>
                        <a href="<?php echo e($row['job_360_url']); ?>" class="font-mono text-sm font-semibold text-erp-primary" <?php $__currentLoopData = $detailLinkAttrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attr => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($attr); ?>="<?php echo e($val); ?>" <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>><?php echo e($row['job_card_number']); ?></a>
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
            <?php $percent = (int) ($row['progress_percent'] ?? 0); ?>
            <div class="production-queue-progress production-queue-progress--mobile">
                <div class="production-queue-progress__track">
                    <div class="production-queue-progress__fill" style="width: <?php echo e($percent); ?>%"></div>
                </div>
                <span class="production-queue-progress__label tabular-nums"><?php echo e($percent); ?>%</span>
            </div>
            <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-slate-600">
                <div><dt class="inline"><?php echo e(__('Due')); ?>:</dt> <dd class="inline font-medium"><?php echo e($row['due_date'] ?? '—'); ?></dd></div>
                <div><dt class="inline"><?php echo e(__('Qty')); ?>:</dt> <dd class="inline font-medium"><?php echo e($row['quantity'] ?? '—'); ?></dd></div>
                <div><dt class="inline"><?php echo e(__('Operator')); ?>:</dt> <dd class="inline font-medium"><?php echo e($row['operator_name']); ?></dd></div>
                <div><dt class="inline"><?php echo e(__('Machine')); ?>:</dt> <dd class="inline font-medium"><?php echo e($row['machine_name']); ?></dd></div>
            </dl>
            <div class="flex flex-wrap gap-2 pt-1">
                <?php echo $__env->make('admin.production.queue.partials.row-actions', ['row' => $row, 'compact' => true, 'inline' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="p-4">
            <?php echo $__env->make('admin.production.queue.partials.empty-state', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    <?php endif; ?>
</div>

<?php if($queues->hasPages()): ?>
    <?php if (isset($component)) { $__componentOriginal26f2fa5d0c1830da52192272c1d5b300 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.table-pagination','data' => ['paginator' => $queues,'turboFrame' => $turboFrame]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.table-pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['paginator' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($queues),'turbo-frame' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($turboFrame)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $attributes = $__attributesOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__attributesOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300)): ?>
<?php $component = $__componentOriginal26f2fa5d0c1830da52192272c1d5b300; ?>
<?php unset($__componentOriginal26f2fa5d0c1830da52192272c1d5b300); ?>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/production/queue/partials/table.blade.php ENDPATH**/ ?>