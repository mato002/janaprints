<?php $versions = $tabData['versions'] ?? []; ?>

<div class="overflow-x-auto">
    <table class="erp-table w-full text-sm">
        <thead>
            <tr>
                <th><?php echo e(__('Version')); ?></th>
                <th><?php echo e(__('Date')); ?></th>
                <th><?php echo e(__('User')); ?></th>
                <th><?php echo e(__('Reason')); ?></th>
                <th><?php echo e(__('Status')); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $versions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $version): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <span class="font-medium"><?php echo e($version['label']); ?></span>
                        <?php if($version['is_current']): ?>
                            <span class="erp-badge ml-1"><?php echo e(__('Current')); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($version['uploaded_at'] ? \Illuminate\Support\Carbon::parse($version['uploaded_at'])->format('Y-m-d H:i') : '—'); ?></td>
                    <td><?php echo e($version['uploaded_by'] ?? '—'); ?></td>
                    <td><?php echo e($version['change_notes']); ?></td>
                    <td><?php echo e($version['status']); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="text-center text-slate-500"><?php echo e(__('No artwork versions yet.')); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\crm\customers\print-specifications\workspace\tabs\artwork_versions.blade.php ENDPATH**/ ?>