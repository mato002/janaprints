<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['rows', 'canAction' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['rows', 'canAction' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="overflow-x-auto exec-table-scroll">
    <table class="exec-table w-full">
        <thead>
            <tr>
                <th><?php echo e(__('Document')); ?></th>
                <th><?php echo e(__('Module')); ?></th>
                <th><?php echo e(__('Requested By')); ?></th>
                <th><?php echo e(__('Branch')); ?></th>
                <th><?php echo e(__('Value')); ?></th>
                <th><?php echo e(__('Age')); ?></th>
                <th><?php echo e(__('Priority')); ?></th>
                <th><?php echo e(__('Actions')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $priorityClass = match ($row['priority'] ?? 'normal') {
                        'critical' => 'exec-approval-priority--critical',
                        'high' => 'exec-approval-priority--high',
                        default => 'exec-approval-priority--normal',
                    };
                ?>
                <tr>
                    <td>
                        <div class="font-medium"><?php echo e($row['document']); ?></div>
                        <div class="text-xs text-slate-500"><?php echo e($row['document_label']); ?></div>
                    </td>
                    <td><?php echo e($row['module']); ?></td>
                    <td><?php echo e($row['requested_by']); ?></td>
                    <td><?php echo e($row['branch']); ?></td>
                    <td><?php echo e($row['value_display']); ?></td>
                    <td><?php echo e($row['age_label']); ?></td>
                    <td>
                        <span class="exec-approval-priority <?php echo e($priorityClass); ?>">
                            <?php echo e(ucfirst($row['priority'])); ?>

                        </span>
                    </td>
                    <td class="erp-table-actions-col">
                        <div class="flex flex-wrap gap-1">
                            <?php if(! empty($row['show_url'])): ?>
                                <a href="<?php echo e($row['show_url']); ?>" data-turbo-frame="erp-main" class="erp-btn-secondary text-xs"><?php echo e(__('View')); ?></a>
                            <?php endif; ?>
                            <?php if($canAction && ($row['can_approve'] ?? false)): ?>
                                <form method="POST" action="<?php echo e(route('admin.executive.approvals.approve', ['kind' => $row['kind'], 'subjectId' => $row['subject_id']])); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="erp-btn-primary text-xs"><?php echo e(__('Approve')); ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if($canAction && ($row['can_reject'] ?? false)): ?>
                                <form method="POST" action="<?php echo e(route('admin.executive.approvals.reject', ['kind' => $row['kind'], 'subjectId' => $row['subject_id']])); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="reason" value="<?php echo e(__('Rejected from executive approval queue.')); ?>">
                                    <button type="submit" class="erp-btn-secondary text-xs text-red-600"><?php echo e(__('Reject')); ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if($canAction && ($row['can_escalate'] ?? false)): ?>
                                <form method="POST" action="<?php echo e(route('admin.executive.approvals.escalate', ['kind' => $row['kind'], 'subjectId' => $row['subject_id']])); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php if(! empty($row['chain_run_id'])): ?>
                                        <input type="hidden" name="chain_run_id" value="<?php echo e($row['chain_run_id']); ?>">
                                    <?php endif; ?>
                                    <button type="submit" class="erp-btn-secondary text-xs"><?php echo e(__('Escalate')); ?></button>
                                </form>
                            <?php endif; ?>
                            <?php if($canAction && ($row['can_delegate'] ?? false)): ?>
                                <a href="<?php echo e(route('admin.executive.approvals.delegate', ['kind' => $row['kind'], 'subjectId' => $row['subject_id']])); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('Delegate')); ?></a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="py-6 text-center text-slate-500"><?php echo e(__('No approvals waiting.')); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\executive\approvals\partials\table.blade.php ENDPATH**/ ?>