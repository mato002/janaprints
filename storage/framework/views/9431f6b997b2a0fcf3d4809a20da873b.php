<?php
    $operatorMode = (bool) ($operatorMode ?? false);
?>

<div class="production-floor-register__table erp-card erp-table-scroll" x-ref="queueTable">
    <table class="erp-table erp-table--grid production-floor-queue-table w-full text-sm">
        <thead>
            <tr>
                <th class="production-floor-col-select w-10">
                    <input
                        type="checkbox"
                        class="rounded border-slate-300"
                        aria-label="<?php echo e(__('Select all jobs on this page')); ?>"
                        @change="toggleSelectAll($event.target.checked)"
                        :checked="allVisibleSelected"
                        :indeterminate="someVisibleSelected && !allVisibleSelected"
                    >
                </th>
                <th class="production-floor-col-job"><?php echo e(__('Job')); ?></th>
                <th><?php echo e(__('Customer')); ?></th>
                <th><?php echo e(__('Product')); ?></th>
                <th><?php echo e(__('Stage')); ?></th>
                <th><?php echo e(__('Machine')); ?></th>
                <th><?php echo e(__('Vendor')); ?></th>
                <th><?php echo e(__('Due')); ?></th>
                <th><?php echo e(__('Priority')); ?></th>
                <th class="erp-table-actions-col"><?php echo e(__('Next step')); ?></th>
            </tr>
        </thead>
        <tbody x-ref="queueBody">
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $rowClasses = ['production-floor-row', 'cursor-pointer', 'hover:bg-slate-50'];
                    if ($row['is_overdue']) {
                        $rowClasses[] = 'production-floor-row--overdue';
                    }
                    if ($row['stage'] === 'qc') {
                        $rowClasses[] = 'production-floor-row--qc';
                    }
                    if ($row['stage'] === 'at_vendor') {
                        $rowClasses[] = 'production-floor-row--vendor';
                    }
                    if ($row['stage'] === 'out') {
                        $rowClasses[] = 'production-floor-row--completed';
                    }
                    if ($row['stage'] === 'on_hold') {
                        $rowClasses[] = 'production-floor-row--hold';
                    }
                ?>
                <tr
                    class="<?php echo e(implode(' ', $rowClasses)); ?>"
                    data-floor-row
                    data-job-key="<?php echo e($row['public_id']); ?>"
                    data-filter-search="<?php echo e(strtolower(implode(' ', array_filter([
                        $row['job_number'] ?? '',
                        $row['customer'] ?? '',
                        $row['product'] ?? '',
                        $row['sku'] ?? '',
                    ])))); ?>"
                    data-filter-stage="<?php echo e($row['stage']); ?>"
                    data-filter-machine-id="<?php echo e($row['machine_id'] ?? ''); ?>"
                    data-filter-vendor="<?php echo e(strtolower(trim($row['vendor'] ?? ''))); ?>"
                    data-filter-priority="<?php echo e($row['priority']); ?>"
                    data-filter-overdue="<?php echo e($row['is_overdue'] ? '1' : '0'); ?>"
                    data-group-machine="<?php echo e($row['machine'] ?? __('Assign')); ?>"
                    data-group-stage="<?php echo e($row['stage_label']); ?>"
                    data-group-priority="<?php echo e($row['priority_label']); ?>"
                    data-group-vendor="<?php echo e($row['vendor'] ?? __('No vendor')); ?>"
                    data-group-due="<?php echo e($row['required_date'] ?? __('No date')); ?>"
                    data-group-operator="<?php echo e($row['work_center'] ?? __('Unassigned')); ?>"
                    data-group-customer="<?php echo e($row['customer'] ?? __('Unknown')); ?>"
                    :class="{ 'production-floor-row--selected': selectedJobs.includes(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>) }"
                    @click="openPanel(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>)"
                >
                    <td class="production-floor-col-select" @click.stop>
                        <input
                            type="checkbox"
                            class="rounded border-slate-300"
                            aria-label="<?php echo e(__('Select job')); ?> <?php echo e($row['job_number']); ?>"
                            :checked="selectedJobs.includes(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>)"
                            @change="toggleJobSelection(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>, $event.target.checked)"
                        >
                    </td>
                    <td class="production-floor-col-job font-mono text-xs">
                        <button type="button" class="break-all text-left text-erp-accent hover:underline" @click.stop="openPanel(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>)">
                            <?php echo e($row['job_number']); ?>

                        </button>
                        <?php if($row['is_overdue']): ?>
                            <span class="production-floor-row__overdue-badge" title="<?php echo e(__('Overdue')); ?>">
                                <span aria-hidden="true">⏰</span>
                                <?php echo e(__('Overdue')); ?>

                            </span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($row['customer'] ?? '—'); ?></td>
                    <td>
                        <span><?php echo e($row['product'] ?? '—'); ?></span>
                        <?php if($row['sku']): ?>
                            <span class="block text-[11px] text-slate-500"><?php echo e($row['sku']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo $__env->make('admin.production.floor.partials.stage-badge', [
                            'stage' => $row['stage'],
                            'label' => $row['stage_label'],
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                    <td @click.stop @mousedown.stop>
                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('machines.assign')): ?>
                            <?php if(count($filter_options['machines']) > 0): ?>
                                <select
                                    name="assigned_machine_asset_id"
                                    class="erp-select production-floor-machine-select w-full text-xs <?php echo e($row['machine_id'] ? 'production-floor-machine-select--assigned' : 'production-floor-machine-select--pending'); ?>"
                                    data-current-value="<?php echo e($row['machine_id'] ?? ''); ?>"
                                    @change="assignMachineInline(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>, $event)"
                                    @click.stop
                                    @mousedown.stop
                                >
                                    <option value=""><?php echo e(__('Assign')); ?></option>
                                    <?php $__currentLoopData = $filter_options['machines']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option
                                            value="<?php echo e($machine['value']); ?>"
                                            <?php if((string) $row['machine_id'] === $machine['value']): echo 'selected'; endif; ?>
                                        >
                                            <?php echo e($machine['label']); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            <?php elseif($row['machine']): ?>
                                <span class="text-xs font-medium text-erp-primary"><?php echo e($row['machine']); ?></span>
                            <?php else: ?>
                                <span class="text-xs text-slate-500"><?php echo e(__('No machines registered')); ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php echo e($row['machine'] ?? '—'); ?>

                        <?php endif; ?>
                    </td>
                    <td class="text-xs">
                        <?php if($row['vendor']): ?>
                            <span class="font-medium"><?php echo e($row['vendor']); ?></span>
                            <?php if($row['vendor_expected_return']): ?>
                                <span class="block text-slate-500"><?php echo e(__('Return')); ?>: <?php echo e($row['vendor_expected_return']); ?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td class="whitespace-nowrap text-xs <?php echo e($row['is_overdue'] ? 'font-semibold text-red-800' : ''); ?>">
                        <?php echo e($row['required_date'] ?? '—'); ?>

                    </td>
                    <td>
                        <?php echo $__env->make('admin.production.floor.partials.priority-badge', [
                            'priority' => $row['priority'],
                            'label' => $row['priority_label'],
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    </td>
                    <td class="erp-table-actions-col" @click.stop>
                        <?php if($row['primary_action']): ?>
                            <?php echo $__env->make('admin.production.floor.partials.next-step-action', [
                                'action' => $row['primary_action'],
                                'jobKey' => $row['public_id'],
                                'operatorMode' => $operatorMode,
                                'buttonClass' => 'erp-btn-primary text-xs py-1 px-2',
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php else: ?>
                            <button type="button" class="erp-btn-secondary text-xs py-1 px-2" @click="openPanel(<?php echo \Illuminate\Support\Js::from($row['public_id'])->toHtml() ?>)"><?php echo e(__('Open')); ?></button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="10" class="py-10 text-center text-slate-500">
                        <?php echo e(__('No jobs match the current filters.')); ?>

                    </td>
                </tr>
            <?php endif; ?>
            <?php if($rows->isNotEmpty()): ?>
                <tr class="production-floor-live-empty" x-ref="liveFilterEmpty" hidden>
                    <td colspan="10" class="py-10 text-center text-slate-500">
                        <?php echo e(__('No jobs match the current filters on this page.')); ?>

                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\production\floor\partials\table.blade.php ENDPATH**/ ?>