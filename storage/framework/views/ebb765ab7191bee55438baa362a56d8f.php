<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['rows', 'title']));

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

foreach (array_filter((['rows', 'title']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if (isset($component)) { $__componentOriginalad5130b5347ab6ecc017d2f5a278b926 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.card','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
    <div class="border-b border-erp-border px-4 py-3 font-semibold"><?php echo e($title); ?></div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full">
            <thead>
                <tr>
                    <th><?php echo e(__('Document')); ?></th>
                    <th><?php echo e(__('Customer')); ?></th>
                    <th><?php echo e(__('Branch')); ?></th>
                    <th><?php echo e(__('Amount')); ?></th>
                    <th><?php echo e(__('Requested By')); ?></th>
                    <th><?php echo e(__('Submitted')); ?></th>
                    <th><?php echo e(__('Age')); ?></th>
                    <th><?php echo e(__('Status')); ?></th>
                    <th><?php echo e(__('Actions')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="font-medium"><?php echo e($row['document']); ?></td>
                        <td><?php echo e($row['customer']); ?></td>
                        <td><?php echo e($row['branch']); ?></td>
                        <td><?php echo e($row['amount']); ?></td>
                        <td><?php echo e($row['requested_by']); ?></td>
                        <td><?php echo e($row['submitted_at']?->format('d M Y')); ?></td>
                        <td><?php echo e($row['age_days']); ?>d</td>
                        <td><?php echo e($row['status_label']); ?></td>
                        <td class="erp-table-actions-col">
                            <div class="flex flex-wrap gap-2">
                                <a href="<?php echo e($row['view_url']); ?>" class="erp-btn-secondary text-xs"><?php echo e(__('View')); ?></a>
                                <?php if($canAction && $row['approve_url']): ?>
                                    <?php if(($row['type'] === 'quotation' && $canApproveQuotations) || ($row['type'] === 'sales_order' && $canConfirmOrders) || ($row['type'] === 'artwork' && $canApproveArtwork)): ?>
                                        <form method="POST" action="<?php echo e($row['approve_url']); ?>" class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="erp-btn-primary text-xs">
                                                <?php echo e($row['type'] === 'sales_order' ? __('Confirm') : __('Approve')); ?>

                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if($canAction && $row['reject_url'] && $row['type'] === 'quotation' && $canRejectQuotations): ?>
                                    <form method="POST" action="<?php echo e($row['reject_url']); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="erp-btn-secondary text-xs text-red-600"><?php echo e(__('Reject')); ?></button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9" class="py-6 text-center text-slate-500"><?php echo e(__('No records.')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $attributes = $__attributesOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__attributesOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926)): ?>
<?php $component = $__componentOriginalad5130b5347ab6ecc017d2f5a278b926; ?>
<?php unset($__componentOriginalad5130b5347ab6ecc017d2f5a278b926); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views/admin/commercial/approvals/partials/table.blade.php ENDPATH**/ ?>